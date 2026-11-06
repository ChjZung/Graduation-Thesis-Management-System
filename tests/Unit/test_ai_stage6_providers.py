"""Offline provider, progress schema and checkpoint checks."""
import os
from pathlib import Path
import sys
from tempfile import TemporaryDirectory
from types import SimpleNamespace
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2] / 'app' / 'Services'
sys.path.insert(0, str(ROOT))
import pymupdf
import ai_gemini_transport as transport
import lab_pdf_summary as lab
from ai_processing import ProcessingError
from ai_report_progress import SCHEMA, validate

SOURCE = 'Nhom da xong viec dang nhap.'
VALUE = {'cong_viec': 'Đã hoàn thành: ' + SOURCE, 'kho_khan': None, 'ke_hoach': None}


class ProviderStageSixTest(unittest.TestCase):
    def test_gemini_default_groq_only_after_503_for_existing_tasks(self):
        pacer = SimpleNamespace(deadline=lab.time.monotonic() + 60)
        with patch.dict(os.environ, {'GROQ_API_KEY': 'mock', 'GROQ_MODEL': 'mock'}, clear=True), patch.object(lab, 'configure_session'):
            provider = lab.WorkerClient('mock', 'mock-gemini', pacer)
            with patch.object(lab.GeminiClient, 'generate', return_value={'data': VALUE}), patch.object(lab, 'generate_groq') as groq:
                self.assertEqual(validate(provider.generate('mock', SCHEMA, 512, 100)['data'], {SOURCE}), VALUE)
                groq.assert_not_called()
            with patch.object(lab.GeminiClient, 'generate', side_effect=lab.ApiError('mock', 'rate_limit', 429, 10)), patch.object(lab, 'generate_groq') as groq:
                with self.assertRaises(lab.ApiError):
                    provider.generate('mock', SCHEMA, 512, 100)
                groq.assert_not_called()
            with patch.object(lab.GeminiClient, 'generate', side_effect=lab.ApiError('mock', 'http_error', 503)), patch.object(lab, 'generate_groq', return_value=(VALUE, 'mock-groq')) as groq:
                self.assertEqual(validate(provider.generate('mock', SCHEMA, 512, 100)['data'], {SOURCE}), VALUE)
                self.assertIn('cong_viec', groq.call_args.args[0])

    def test_groq_rejects_truncated_or_unfinished_response(self):
        import json
        session = SimpleNamespace(remaining=lambda seconds: seconds, before_http=lambda *a: None, record_usage=lambda *a: None)
        for reason in ['length', 'tool_calls', None, 'stop']:
            with self.subTest(reason=reason):
                response = SimpleNamespace(status_code=200, headers={}, text=json.dumps({'choices': [
                    {'finish_reason': reason, 'message': {'content': json.dumps(VALUE)}}]}))
                client = SimpleNamespace(post=lambda *a, **kw: response)
                with patch.object(transport, 'current_session', return_value=session):
                    call = lambda: transport.generate_groq('prompt', 'mock', 'mock', transport.time.monotonic()+30, client, lambda s:s)
                    if reason == 'stop':
                        self.assertEqual(call()[0], VALUE)
                    else:
                        with self.assertRaises(ProcessingError):
                            call()

    def test_pdf_progress_uses_cached_validated_result_on_second_run(self):
        with TemporaryDirectory() as directory:
            path = Path(directory) / 'fixture.pdf'
            with pymupdf.open() as pdf:
                page = pdf.new_page()
                page.insert_text((60, 140), SOURCE)
                pdf.save(path)
            calls = []
            provider = SimpleNamespace(count_tokens=lambda *_: 100,
                                       generate=lambda *_: (calls.append(1) or {'data': VALUE}))
            with patch.object(lab, 'run_summary_with_provenance', return_value={'status': 'partial'}), patch.object(
                    lab, 'convert_result', side_effect=lambda *a: {'data': {'chapters': []}}):
                for _ in range(2):
                    output = lab.run_pipeline(str(path), 'mock', 'mock', str(Path(directory) / 'cache'),
                                              provider_factory=lambda: provider)
                    self.assertEqual(output['data']['progress_v1'], VALUE)
            self.assertEqual(len(calls), 1)


if __name__ == '__main__':
    unittest.main()

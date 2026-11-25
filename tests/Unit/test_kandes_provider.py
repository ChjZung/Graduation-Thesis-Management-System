"""Offline contract tests for Kandes, including the existing PDF and DOCX entry points."""
from contextlib import redirect_stderr
from email.message import Message
import io
import json
import shutil
from pathlib import Path
import sys
import tempfile
import unittest
from unittest.mock import patch
from urllib.error import HTTPError

ROOT = Path(__file__).resolve().parents[2] / 'app' / 'Services'
sys.path.insert(0, str(ROOT))
import pymupdf
import lab_pdf_summary as lab
import ai_gemini_transport as transport
from ai_processing import CallSession, ProcessingError
from ai_runtime.kandes import KandesClient, probe, request_slot

PROMPT = 'Nhóm đã hoàn thành màn hình quản lý đề tài.'
SCHEMA = {'type': 'object', 'properties': {'check': {'type': 'string'}}, 'required': ['check']}


def response(content, finish='stop'):
    return io.BytesIO(json.dumps({
        'choices': [{'message': {'content': content}, 'finish_reason': finish}],
        'usage': {'prompt_tokens': 12, 'completion_tokens': 5, 'total_tokens': 17},
    }).encode())


def http_error(status, code='', retry_after=None):
    headers = Message()
    if retry_after is not None:
        headers['Retry-After'] = str(retry_after)
    return HTTPError('https://example.invalid', status, 'mock', headers,
                     io.BytesIO(json.dumps({'error': {'code': code, 'message': 'private fixture'}}).encode()))


class KandesTest(unittest.TestCase):
    def setUp(self):
        env = patch.dict('os.environ', {'LLM_PROVIDER': 'kandes', 'KANDES_API_KEY': 'fake-private-key',
                                        'KANDES_BASE_URL': 'https://example.invalid/v1'}, clear=True)
        env.start()
        self.addCleanup(env.stop)
        self.stderr = io.StringIO()
        context = redirect_stderr(self.stderr)
        context.__enter__()
        self.addCleanup(context.__exit__, None, None, None)
        self.client = KandesClient('fake-private-key')

    def test_posts_bearer_json_schema_and_normalizes_usage_without_logging_content(self):
        with patch.object(self.client._opener, 'open', return_value=response('{"check":"ok"}')) as send:
            tokens = self.client.count_tokens(PROMPT, SCHEMA, 256)
            self.assertGreater(tokens, self.client.count_tokens(PROMPT, None, 256))
            self.assertEqual(self.client.generate(PROMPT, SCHEMA, 256, tokens), {
                'data': {'check': 'ok'}, 'usage': {'promptTokenCount': 12, 'candidatesTokenCount': 5,
                    'totalTokenCount': 17, 'provider_model': self.client.model}, 'finish_reason': 'STOP',
            })
        request = send.call_args.args[0]
        payload = json.loads(request.data)
        self.assertEqual(request.full_url, 'https://example.invalid/v1/chat/completions')
        self.assertEqual(request.get_header('Authorization'), 'Bearer fake-private-key')
        self.assertEqual(payload['messages'][-1]['content'], PROMPT)
        self.assertIn(json.dumps(SCHEMA, separators=(',', ':')), payload['messages'][0]['content'])
        self.assertEqual(payload['response_format'], {'type': 'json_object'})
        self.assertNotIn('fake-private-key', self.stderr.getvalue())
        self.assertNotIn(PROMPT, self.stderr.getvalue())
        self.assertIn('"input_tokens": 12', self.stderr.getvalue())
        self.assertIn('"output_tokens": 5', self.stderr.getvalue())

    def test_transient_failures_retry_once_with_backoff(self):
        for failure in (http_error(429, retry_after=4), http_error(502), http_error(503), TimeoutError()):
            with self.subTest(failure=failure), patch.object(self.client._opener, 'open', side_effect=[
                    failure, response('{"check":"ok"}')]) as send, patch('ai_runtime.kandes.time.sleep') as sleep:
                self.assertEqual(self.client.generate(PROMPT, SCHEMA, 256, 20)['data'], {'check': 'ok'})
                self.assertEqual(send.call_count, 2)
                sleep.assert_called_once_with(4 if getattr(failure, 'code', None) == 429 else 2)
        with patch.object(self.client._opener, 'open', side_effect=[TimeoutError(), TimeoutError()]) as send, patch(
                'ai_runtime.kandes.time.sleep'):
            with self.assertRaises(lab.ApiError):
                self.client.generate(PROMPT, SCHEMA, 256, 20)
            self.assertEqual(send.call_count, 2)

    def test_auth_billing_and_long_retry_after_do_not_resend(self):
        cases = [(http_error(401), 'authentication'), (http_error(402), 'billing'),
                 (http_error(429, 'insufficient_quota'), 'billing'), (http_error(503, retry_after=60), 'http_error')]
        for error, expected in cases:
            with self.subTest(code=expected), patch.object(self.client._opener, 'open', side_effect=error) as send, patch(
                    'ai_runtime.kandes.time.sleep') as sleep:
                with self.assertRaises(lab.ApiError) as raised:
                    self.client.generate(PROMPT, SCHEMA, 256, 20)
                self.assertEqual(raised.exception.code, expected)
                if error.code == 503:
                    self.assertEqual(raised.exception.retry_after, 60)
                send.assert_called_once()
                sleep.assert_not_called()
        self.assertNotIn('private fixture', self.stderr.getvalue())

    def test_rejects_invalid_or_truncated_model_output(self):
        for content, reason, code in [('text', 'stop', 'invalid_output'), ('[]', 'stop', 'invalid_output'),
                                      ('{"check":"ok"}', 'length', 'incomplete_response')]:
            with self.subTest(content=content, reason=reason), patch.object(
                    self.client._opener, 'open', return_value=response(content, reason)):
                with self.assertRaises(lab.ApiError) as raised:
                    self.client.generate(PROMPT, SCHEMA, 256, 20)
                self.assertEqual(raised.exception.code, code)

    def test_accepts_a_single_json_fence_but_rejects_surrounding_prose(self):
        fence = chr(96) * 3
        valid = fence + 'json\n{"check":"ok"}\n' + fence
        with patch.object(self.client._opener, 'open', side_effect=[response(valid), response('Extra prose\n' + valid)]):
            self.assertEqual(self.client.generate(PROMPT, SCHEMA, 256, 20)['data'], {'check': 'ok'})
            with self.assertRaises(lab.ApiError) as raised:
                self.client.generate(PROMPT, SCHEMA, 256, 20)
            self.assertEqual(raised.exception.code, 'invalid_output')

    def test_nested_checkpoint_and_review_page_cache_support_long_paths(self):
        from ai_runtime.requirement_review.source_index import build_source_index

        with tempfile.TemporaryDirectory() as directory:
            temporary = Path(directory)
            pdf = temporary / 'fixture.pdf'
            with pymupdf.open() as doc:
                doc.new_page().insert_text((60, 80), PROMPT)
                doc.save(pdf)
            first = temporary / ('a' * 60)
            root = first / ('b' * 60) / ('c' * 60)
            target = root / 'pdf_lab' / ('d' * 64) / 'review_0123456789abcdef.json'
            self.assertGreater(len(str(target)), 260)

            try:
                lab.atomic_json(target, {'items': ['saved']})
                self.assertEqual(lab.load_state(target), {'items': ['saved']})
                pages = lab.checkpoint_path(root) / 'verified_pdf_review' / ('pages_' + 'e' * 40)
                index = build_source_index(pdf, page_checkpoint_dir=pages)
                self.assertEqual(index['document']['total_pages'], 1)
                self.assertTrue(any(pages.glob('*.json')))
            finally:
                shutil.rmtree(lab.checkpoint_path(first), ignore_errors=True)

    def test_probe_sends_text_then_structured_json_only(self):
        with patch('ai_runtime.kandes.urllib.request.build_opener') as opener:
            send = opener.return_value.open
            send.side_effect = [response('OK'), response('{"check":"ok"}')]
            self.assertEqual(probe()['checks'], ['text', 'json_object'])
            first, second = [json.loads(call.args[0].data) for call in send.call_args_list]
            self.assertNotIn('response_format', first)
            self.assertEqual(second['response_format'], {'type': 'json_object'})

    def test_pdf_worker_uses_kandes_and_stops_on_billing(self):
        with tempfile.TemporaryDirectory() as directory:
            path = Path(directory) / 'fixture.pdf'
            with pymupdf.open() as pdf:
                pdf.new_page().insert_text((60, 140), 'Nhom da xong viec dang nhap.')
                pdf.save(path)
            with patch('ai_runtime.kandes.urllib.request.build_opener') as opener, patch.object(
                    lab.WorkerClient, '__init__', side_effect=AssertionError('Unexpected Gemini call')):
                send = opener.return_value.open
                send.side_effect = http_error(429, 'insufficient_quota')
                result = lab.run_pipeline(str(path), '', self.client.model, str(Path(directory) / 'cache'))
                self.assertEqual(result['data']['processing_errors'][0]['code'], 'billing')
                send.assert_called_once()

    def test_legacy_session_reuses_terminal_error_and_avoids_gemini(self):
        session = CallSession()
        with patch.object(transport, 'current_session', return_value=session), patch.object(
                transport, 'generate_groq', side_effect=AssertionError('Unexpected Groq call')), patch(
                'ai_runtime.kandes.urllib.request.build_opener') as opener:
            send = opener.return_value.open
            send.side_effect = http_error(402)
            for prompt in (PROMPT, 'Second prompt'):
                with self.assertRaises(ProcessingError) as raised:
                    session.call(lambda text, key, **opts: transport.generate(
                        text, key, opts['model'], opts['timeout'], None, str), prompt, 'fake-private-key', self.client.model)
                self.assertEqual(raised.exception.code, 'billing')
            send.assert_called_once()

    def test_project_slot_is_exclusive_and_released(self):
        with tempfile.TemporaryDirectory() as directory:
            lock = Path(directory) / 'request.lock'
            with request_slot(lock), self.assertRaises(lab.ApiError):
                with request_slot(lock):
                    self.fail('A second request acquired the lock')
            with request_slot(lock):
                self.assertTrue(lock.exists())


if __name__ == '__main__':
    unittest.main()

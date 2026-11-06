"""Offline checks for PDF provider policy during temporary outages."""
from pathlib import Path
import sys
import unittest
from types import SimpleNamespace
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2] / 'app' / 'Services'
sys.path.insert(0, str(ROOT))
import lab_pdf_summary as lab
from ai_processing import ProcessingError


class PdfProviderOutageTest(unittest.TestCase):
    def test_gemini_429_or_timeout_does_not_trigger_unapproved_fallback(self):
        for error in [lab.ApiError('rate limit','rate_limit',429), lab.ApiError('timeout','provider_timeout')]:
            with self.subTest(code=error.code), patch.dict('os.environ', {'GROQ_MODEL':'mock','GROQ_API_KEY':'fake'}, clear=True), patch.object(lab,'configure_session'):
                client = lab.WorkerClient('fake','gemini-mock',SimpleNamespace(deadline=lab.time.monotonic()+60))
                with patch.object(lab.GeminiClient,'generate',side_effect=error), patch.object(
                        lab,'generate_groq',side_effect=ProcessingError('rate_limit',http_status=429)) as groq:
                    with self.assertRaises(lab.ApiError) as raised:
                        client.generate('fixture',{},512,10)
                    self.assertIs(raised.exception,error)
                    groq.assert_not_called()

    def test_gemini_503_can_fallback_but_keeps_groq_timeout_separate(self):
        with patch.dict('os.environ', {'GROQ_MODEL':'mock','GROQ_API_KEY':'fake'}, clear=True), patch.object(lab,'configure_session'):
            client = lab.WorkerClient('fake','gemini-mock',SimpleNamespace(deadline=lab.time.monotonic()+60))
            with patch.object(lab.GeminiClient,'generate',side_effect=lab.ApiError('busy','http_error',503)) as gemini, patch.object(
                    lab,'generate_groq',side_effect=ProcessingError('timeout',model='groq:mock')) as groq:
                with self.assertRaises(lab.ApiError) as raised:
                    client.generate('fixture',{},512,10)
                detail = lab.processing_error(raised.exception)
                self.assertEqual(detail['code'],'timeout')
                self.assertEqual(detail['primary_http_status'],503)
                self.assertEqual(detail['model'],'groq:mock')
                gemini.assert_called_once()
                groq.assert_called_once()
            with patch.object(lab.GeminiClient,'model_info') as meta, self.assertRaises(lab.ApiError):
                client.model_info()
            meta.assert_not_called()


if __name__ == '__main__':
    unittest.main()

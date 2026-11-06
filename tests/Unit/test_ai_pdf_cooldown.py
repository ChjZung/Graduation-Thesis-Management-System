"""Offline checks for provider cooldown and fallback metadata."""
from pathlib import Path
import sys
from tempfile import TemporaryDirectory
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2] / 'app' / 'Services'
sys.path.insert(0, str(ROOT))
from ai_rate_limit import ProjectRateLimiter
from ai_processing import CallSession, ProcessingError
from ai_gemini_transport import quota_detail
import lab_pdf_summary as lab


class ProviderCooldownTest(unittest.TestCase):
    def test_two_workers_see_remaining_time_and_expiry_does_not_move(self):
        with TemporaryDirectory() as root:
            path = Path(root) / 'quota.sqlite'
            a = ProjectRateLimiter(path, 'scope')
            b = ProjectRateLimiter(path, 'scope')
            model = 'groq:mock'
            self.assertEqual(a.defer(model, {'http_status':429, 'retry_after':1745}, now=1000), 2745)
            for worker in [a, b]:
                wait, detail = worker.reserve(model, 100, now=1660)
                self.assertEqual(wait, 1085)
                self.assertEqual(detail['retry_after'], 1085)
                self.assertEqual(detail['cooldown_until'], 2745)
                self.assertEqual(detail['original_retry_after'], 1745)
            self.assertEqual(a.reserve(model, 100, now=2745), (0, None))
            self.assertEqual(a.defer(model, {'http_status':429, 'retry_after':30}, now=2800), 2830)
            self.assertEqual(b.reserve(model, 100, now=2805)[0], 25)

    def test_groq_request_stops_at_local_cooldown_before_http(self):
        from unittest.mock import Mock
        from ai_gemini_transport import generate_groq
        with TemporaryDirectory() as root, patch.dict('os.environ', {}, clear=True):
            session = CallSession(budget=30)
            session.groq_limiter = ProjectRateLimiter(Path(root) / 'quota.sqlite', 'scope')
            session.groq_limiter.defer('groq:mock', {'http_status':429, 'retry_after':1745}, now=1000)
            http = Mock()
            with patch('ai_gemini_transport.current_session', return_value=session), patch('ai_rate_limit.time.time', return_value=1660):
                with self.assertRaises(ProcessingError) as raised:
                    generate_groq('fixture', 'fake', 'mock', lab.time.monotonic()+20, http, lambda value:value)
            http.post.assert_not_called()
            self.assertEqual(raised.exception.details['retry_after'], 1085)
            self.assertEqual(raised.exception.details['cooldown_until'], 2745)

    def test_malformed_retry_after_uses_default(self):
        for value in ['NaN', 'inf', '-100', 'unknown', None]:
            with self.subTest(value=value):
                self.assertEqual(quota_detail({}, {'Retry-After':value}, 'mock')['retry_after'], 60)

    def test_cached_groq_cooldown_preserves_gemini_503_without_network(self):
        from types import SimpleNamespace
        metadata = {'model':'groq:mock', 'http_status':429, 'cooldown_cached':True,
                    'cooldown_until':2745, 'retry_after':1085, 'original_retry_after':1745}
        with patch.dict('os.environ', {'GROQ_MODEL':'mock','GROQ_API_KEY':'fake'}, clear=True), patch.object(lab, 'configure_session'):
            client = lab.WorkerClient('fake', 'gemini-mock', SimpleNamespace(deadline=lab.time.monotonic()+30))
            with patch.object(lab.GeminiClient, 'generate', side_effect=lab.ApiError('busy','http_error',503)), patch.object(
                    lab, 'generate_groq', side_effect=ProcessingError('rate_limit', **metadata)):
                with self.assertRaises(lab.ApiError) as raised:
                    client.generate('fixture', {}, 512, 100)
            details = lab.processing_error(lab.ApiError.from_details(raised.exception.details()))
            self.assertEqual(details['primary_http_status'], 503)
            self.assertEqual(details['http_status'], 429)
            self.assertEqual(details['cooldown_until'], 2745)
            self.assertEqual(details['retry_after'], 1085)


if __name__ == '__main__':
    unittest.main()

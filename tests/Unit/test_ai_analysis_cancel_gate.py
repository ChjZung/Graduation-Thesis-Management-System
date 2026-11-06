"""Offline cancellation gate for both providers."""
from pathlib import Path
from tempfile import TemporaryDirectory
from unittest.mock import patch
import sys
import unittest

sys.path.insert(0, str(Path(__file__).resolve().parents[2] / 'app' / 'Services'))
from ai_processing import CallSession, ProcessingError
from lab_pdf_summary import ApiError, SharedPacer
import time


class AnalysisCancellationGateTest(unittest.TestCase):
    def test_marker_blocks_gemini_and_groq(self):
        with TemporaryDirectory() as directory:
            marker = Path(directory) / 'cancel'
            with patch.dict('os.environ', {'AI_CANCEL_FILE': str(marker)}, clear=True):
                pacer = SharedPacer('gemini-test', time.monotonic()+120, lambda **kw: None)
                session = CallSession()
                pacer.before_request()
                session.before_http('groq:test', 1)
                marker.touch()
                with self.assertRaises(ApiError):
                    pacer.before_request()
                with self.assertRaises(ProcessingError):
                    session.before_http('groq:test', 1)


if __name__ == '__main__':
    unittest.main()

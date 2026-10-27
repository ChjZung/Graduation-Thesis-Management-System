"""Opt-in Gemini execution, separate from the offline segmentation core."""
from .provider import GeminiClient, ApiError, RequestPacer
from .summarizer import RunSettings, run_summary

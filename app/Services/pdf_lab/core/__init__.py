"""Offline, UI-independent PDF segmentation API."""
from .segmentation_pipeline import segment_pdf
from .schemas import Settings, SegmentationError
from .ai_packaging import PackagingSettings, prepare_ai_inputs

__all__ = ['segment_pdf', 'Settings', 'SegmentationError', 'PackagingSettings', 'prepare_ai_inputs']

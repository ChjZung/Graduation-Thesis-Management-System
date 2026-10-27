from dataclasses import dataclass, asdict


class SegmentationError(ValueError):
    def __init__(self, message, code='invalid_pdf'):
        super().__init__(message)
        self.code = code


@dataclass(frozen=True)
class Settings:
    target_tokens: int = 2500
    max_tokens: int = 3000
    overlap_tokens: int = 200
    use_fonts: bool = True
    use_outline: bool = True
    heading_threshold: float = 3.4
    max_upload_mb: int = 50
    max_pages: int = 1000

    def __post_init__(self):
        if not 20 <= self.target_tokens <= self.max_tokens <= 20000:
            raise SegmentationError('Cần 20 ≤ target ≤ maximum ≤ 20.000.', 'invalid_settings')
        if not 0 <= self.overlap_tokens < self.target_tokens // 2:
            raise SegmentationError('Overlap phải không âm và nhỏ hơn một nửa target.', 'invalid_settings')
        if not 1 <= self.max_upload_mb <= 200 or not 1 <= self.max_pages <= 2000:
            raise SegmentationError('Giới hạn file/trang không hợp lệ.', 'invalid_settings')
        if not 1 <= self.heading_threshold <= 8:
            raise SegmentationError('Ngưỡng heading phải từ 1 đến 8.', 'invalid_settings')

    def to_dict(self):
        return asdict(self)

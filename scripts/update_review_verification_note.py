from pathlib import Path
p=Path('VERIFIED_REQUIREMENT_REVIEW.md');s=p.read_text('utf-8').replace('22 Python tests','23 Python tests');p.write_text(s,'utf-8')

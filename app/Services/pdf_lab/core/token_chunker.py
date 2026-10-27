"""Replaceable offline lexical estimator. Counts words/numbers/punctuation, NOT Gemini tokens."""
import re
from typing import Protocol
from .section_tree import page_for_offset


class TokenCounter(Protocol):
    name: str
    def spans(self, text): ...
    def count(self, text): ...


class LexicalCounter:
    name = 'unicode_lexical_v1 (ước tính local; không tương đương tokenizer Gemini)'
    pattern = re.compile(r'\w+|[^\w\s]', re.UNICODE)

    def spans(self, text):
        return [(m.start(), m.end()) for m in self.pattern.finditer(text)]

    def count(self, text):
        return sum(1 for _ in self.pattern.finditer(text))


def chunk_sections(document, sections, settings, counter=None):
    counter = counter or LexicalCounter()
    chunks = []
    by_id = {s['section_id']: s for s in sections}
    for section in sections:
        text = section['content']; spans = counter.spans(text)
        if not text:
            continue
        boundaries = [0] + [start for start, _ in spans[1:]] + [len(text)] if spans else [0, len(text)]
        n = len(spans)
        cursor, index = 0, 0
        while cursor < n or (n == 0 and index == 0):
            start_token = max(0, cursor - settings.overlap_tokens) if index else 0
            end_token = min(n, start_token + settings.target_tokens)
            reason = 'whole_section' if index == 0 and end_token == n else 'token_window'
            if end_token < n:
                lower = max(cursor + 1, start_token + int(settings.target_tokens * .65))
                for delimiter, label in [('\n', 'line_or_paragraph_boundary'), ('. ', 'sentence_boundary')]:
                    options = [j for j in range(lower, end_token + 1)
                               if delimiter in text[spans[j - 1][1]:boundaries[j]] or
                               (delimiter == '. ' and text[max(0, boundaries[j] - 2):boundaries[j]] == '. ')]
                    if options:
                        end_token = options[-1]; reason = label; break
            if n == 0:
                start, end, novel_start = 0, len(text), 0
            else:
                start, end, novel_start = boundaries[start_token], boundaries[end_token], boundaries[cursor]
            ancestor = section
            while ancestor['parent_section_id']:
                ancestor = by_id[ancestor['parent_section_id']]
            content = text[start:end]
            chunks.append({'chunk_id': f"{section['section_id']}-{index + 1:03}",
                           'section_id': section['section_id'], 'section_number': section['section_number'],
                           'section_title': section['section_title'], 'chapter_number': ancestor['section_number'],
                           'chapter_title': ancestor['section_title'], 'chunk_index': index + 1,
                           'order_index': len(chunks), 'start': section['start'] + start,
                           'end': section['start'] + end, 'novel_start': section['start'] + novel_start,
                           'start_page': page_for_offset(document, section['start'] + start),
                           'end_page': page_for_offset(document, section['start'] + end, True),
                           'token_count': counter.count(content), 'overlap_tokens': cursor - start_token,
                           'overlap_chars': novel_start - start, 'split_reason': reason,
                           'warning': reason == 'token_window', 'content': content})
            cursor = end_token; index += 1
            if n == 0:
                break
        section['token_count'] = counter.count(text)
        section['chunk_count'] = index
    return chunks

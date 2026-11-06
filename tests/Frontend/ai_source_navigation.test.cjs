const assert = require('node:assert/strict');
const test = require('node:test');
const {normalize, rank, references} = require('../../public/js/ai_source_navigation');

const rows = [
    {page: 12, quote: 'Dữ liệu lấy từ kho câu hỏi.', section: 'Thiết kế', type: 'Thiết kế'},
    {page: 25, quote: 'Quản lý ngân hàng câu hỏi lưu kết quả.', section: 'Triển khai', type: 'Triển khai'},
    {page: 33, quote: 'Kiểm thử ngân hàng câu hỏi bằng dữ liệu mẫu.', section: 'Kiểm thử', type: 'Kiểm thử'},
];

test('normalize Vietnamese, prefer direct terms, find aliases across sections', () => {
    const query = normalize('  Ngân-hàng CÂU HỎI ');
    assert.equal(query, 'ngan hang cau hoi');
    assert.deepEqual(rank(rows, query).map(row => row.page), [25, 33, 12]);
    assert.deepEqual(rank(rows, query, 'Kiểm thử').map(row => row.page), [33]);
    assert.deepEqual(rank([{quote: 'blogin'}], 'login'), []);
});

test('asset search uses the caption and does not infer from the section title', () => {
    const asset = {quote: 'Hình 2.1 – Quy trình tiếp nhận', section: 'Đăng nhập'};
    assert.deepEqual(rank([asset], 'dang nhap', '', true), []);
});

test('link only a unique caption explicitly referenced by a matching passage', () => {
    const asset = {quote: 'Hình 3.2 – Use Case', caption_label: 'Hình 3.2'};
    const other = {quote: 'Hình 3.20 – Khôi phục dữ liệu', caption_label: 'Hình 3.20'};
    const row = {quote: 'Chức năng đăng nhập được mô tả tại Hình 3.2.'};
    assert.deepEqual(references([row], [asset, other]), [{asset, row}]);
    assert.deepEqual(references([asset], [asset]), []);
    assert.deepEqual(references([row], [asset, {...asset}]), []);
    assert.deepEqual(references([row], [{...asset, basis: 'table'}]), []);
});

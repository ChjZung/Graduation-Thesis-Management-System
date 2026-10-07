{{-- ========================================================
   HUIT - MODAL XEM NHANH ĐỀ CƯƠNG CHI TIẾT (.PDF / .DOCX)
   Sử dụng: @include('partials.modal_preview_decuong')
   Gọi JS: quickPreviewOutline(fileUrl, fileName, topicTitle)
======================================================== --}}

<div class="modal fade" id="modalQuickPreviewDeCuong" tabindex="-1" aria-labelledby="modalQuickPreviewLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw; height: 92vh; margin: 4vh auto;">
        <div class="modal-content h-100 border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
            
            <!-- Modal Header -->
            <div class="modal-header py-2 px-3 text-white" style="background: linear-gradient(135deg, #00529b 0%, #003366 100%); flex-shrink: 0;">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <span class="badge bg-white text-primary fw-bold px-2 py-1" id="quickPreviewTypeBadge" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-file me-1"></i> ĐỀ CƯƠNG
                    </span>
                    <h6 class="modal-title text-truncate fw-bold mb-0 text-white" id="modalQuickPreviewLabel" style="font-size: 1rem;" title="Xem nhanh đề cương">
                        Xem Nhanh Đề Cương Chi Tiết
                    </h6>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
                    <a href="#" id="quickPreviewOpenTabBtn" target="_blank" class="btn btn-sm btn-outline-light py-1 px-2.5 rounded-pill" title="Mở trong tab mới của trình duyệt">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> <span class="d-none d-sm-inline">Mở tab mới</span>
                    </a>
                    <a href="#" id="quickPreviewDownloadBtn" download class="btn btn-sm btn-warning text-dark fw-semibold py-1 px-2.5 rounded-pill" title="Tải file về máy tính">
                        <i class="fa-solid fa-download me-1"></i> <span class="d-none d-sm-inline">Tải về</span>
                    </a>
                    <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-0 position-relative bg-light" style="flex: 1 1 auto; overflow: hidden; min-height: 0;">
                
                <!-- Loading State Spinner -->
                <div id="quickPreviewLoading" class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white" style="z-index: 20;">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3.2rem; height: 3.2rem;">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <div class="fw-bold text-dark fs-6" id="quickPreviewLoadingText">Đang tải và chuẩn bị hiển thị đề cương...</div>
                    <div class="small text-muted mt-1">Hệ thống đang mở tài liệu trực tiếp trên trình duyệt</div>
                </div>

                <!-- PDF Preview Container -->
                <div id="quickPreviewPdfContainer" class="w-100 h-100 d-none" style="position: absolute; top:0; left:0; right:0; bottom:0;">
                    <iframe id="quickPreviewIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>

                <!-- Word (.DOCX) Preview Container -->
                <div id="quickPreviewDocxContainer" class="w-100 h-100 overflow-auto p-3 p-md-4 d-none" style="background: #525659; position: absolute; top:0; left:0; right:0; bottom:0;">
                    <div class="mx-auto bg-white shadow-lg p-4 p-md-5 my-2" style="max-width: 900px; min-height: 100%; border-radius: 4px;" id="quickPreviewDocxContent">
                    </div>
                </div>

                <!-- Fallback / Unsupported Container -->
                <div id="quickPreviewFallbackContainer" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center d-none" style="position: absolute; top:0; left:0; right:0; bottom:0; background: #f8f9fa;">
                    <div class="p-4 bg-white rounded-4 shadow-sm border" style="max-width: 520px;">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning" style="width: 70px; height: 70px;">
                                <i class="fa-solid fa-file-word fa-2x"></i>
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Tài Liệu Đề Cương</h5>
                        <p class="text-muted small mb-4" id="quickPreviewFallbackMsg">
                            Tệp tài liệu đề cương đã sẵn sàng. Bạn có thể mở trực tiếp trong tab mới hoặc tải về máy tính để xem đầy đủ bằng Microsoft Word.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" id="quickPreviewFallbackOpenBtn" target="_blank" class="btn btn-outline-primary px-3 rounded-pill">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở tab mới
                            </a>
                            <a href="#" id="quickPreviewFallbackDownloadBtn" download class="btn btn-primary px-3 rounded-pill">
                                <i class="fa-solid fa-download me-1"></i> Tải về máy
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer (Small status line) -->
            <div class="modal-footer py-1 px-3 bg-white border-top d-flex justify-content-between align-items-center" style="flex-shrink: 0;">
                <div class="small text-muted" id="quickPreviewFooterInfo">
                    <i class="fa-regular fa-circle-question me-1 text-primary"></i> Nhấn phím <kbd>ESC</kbd> hoặc nút đóng để quay lại màn hình xét duyệt.
                </div>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS cho hiển thị văn bản Word DOCX chuẩn tài liệu A4 */
#quickPreviewDocxContent {
    font-family: "Times New Roman", Times, serif;
    font-size: 13.5pt;
    line-height: 1.6;
    color: #1a1a1a;
}
#quickPreviewDocxContent h1, 
#quickPreviewDocxContent h2, 
#quickPreviewDocxContent h3, 
#quickPreviewDocxContent h4 {
    color: #003366;
    font-weight: 700;
    margin-top: 1.2rem;
    margin-bottom: 0.6rem;
}
#quickPreviewDocxContent h1 { font-size: 18pt; text-align: center; }
#quickPreviewDocxContent h2 { font-size: 15pt; }
#quickPreviewDocxContent h3 { font-size: 14pt; }
#quickPreviewDocxContent p {
    margin-bottom: 0.8rem;
    text-align: justify;
}
#quickPreviewDocxContent table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.2rem 0;
    font-size: 12.5pt;
}
#quickPreviewDocxContent table th,
#quickPreviewDocxContent table td {
    border: 1px solid #444;
    padding: 8px 12px;
    vertical-align: top;
}
#quickPreviewDocxContent table th {
    background-color: #f1f4f8;
    font-weight: bold;
}
#quickPreviewDocxContent ul, 
#quickPreviewDocxContent ol {
    padding-left: 2rem;
    margin-bottom: 0.8rem;
}
#quickPreviewDocxContent img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 1rem auto;
}
</style>

<!-- Nạp thư viện Mammoth.js đọc file Word .docx trực tiếp phía trình duyệt -->
<script src="{{ asset('js/mammoth.browser.min.js') }}"></script>

<script>
/**
 * Xem Nhanh Đề Cương Chi Tiết trong Modal
 * @param {string} fileUrl    Đường dẫn URL của file đề cương (PDF hoặc DOCX/DOC)
 * @param {string} fileName   Tên file đề cương hiển thị
 * @param {string} topicTitle Tên đề tài khóa luận
 */
function quickPreviewOutline(fileUrl, fileName, topicTitle) {
    if (!fileUrl) {
        alert('Không tìm thấy đường dẫn tệp đề cương.');
        return;
    }

    const modalEl = document.getElementById('modalQuickPreviewDeCuong');
    if (!modalEl) {
        console.error('Không tìm thấy modal #modalQuickPreviewDeCuong trên trang.');
        window.open(fileUrl, '_blank');
        return;
    }

    const titleEl = document.getElementById('modalQuickPreviewLabel');
    const badgeEl = document.getElementById('quickPreviewTypeBadge');
    const openTabBtn = document.getElementById('quickPreviewOpenTabBtn');
    const downloadBtn = document.getElementById('quickPreviewDownloadBtn');
    
    const loadingEl = document.getElementById('quickPreviewLoading');
    const loadingText = document.getElementById('quickPreviewLoadingText');
    const pdfContainer = document.getElementById('quickPreviewPdfContainer');
    const pdfIframe = document.getElementById('quickPreviewIframe');
    const docxContainer = document.getElementById('quickPreviewDocxContainer');
    const docxContent = document.getElementById('quickPreviewDocxContent');
    const fallbackContainer = document.getElementById('quickPreviewFallbackContainer');
    const fallbackMsg = document.getElementById('quickPreviewFallbackMsg');
    const fallbackOpenBtn = document.getElementById('quickPreviewFallbackOpenBtn');
    const fallbackDownloadBtn = document.getElementById('quickPreviewFallbackDownloadBtn');

    // Cập nhật các liên kết mở tab và tải về
    openTabBtn.href = fileUrl;
    downloadBtn.href = fileUrl;
    fallbackOpenBtn.href = fileUrl;
    fallbackDownloadBtn.href = fileUrl;

    const safeName = fileName || 'De_Cuong_Chi_Tiet';
    downloadBtn.setAttribute('download', safeName);
    fallbackDownloadBtn.setAttribute('download', safeName);

    // Tiêu đề modal
    titleEl.textContent = topicTitle ? ('Đề cương: ' + topicTitle) : ('Xem nhanh: ' + safeName);
    titleEl.title = titleEl.textContent;

    // Reset giao diện trước khi tải
    loadingEl.classList.remove('d-none');
    pdfContainer.classList.add('d-none');
    docxContainer.classList.add('d-none');
    fallbackContainer.classList.add('d-none');
    pdfIframe.src = '';
    docxContent.innerHTML = '';

    // Nhận diện phần mở rộng file
    const cleanUrl = fileUrl.split('?')[0].toLowerCase();
    const isPdf = cleanUrl.endsWith('.pdf');
    const isDocx = cleanUrl.endsWith('.docx');
    const isDoc = cleanUrl.endsWith('.doc');

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    if (isPdf) {
        badgeEl.className = 'badge bg-danger text-white fw-bold px-2 py-1';
        badgeEl.innerHTML = '<i class="fa-solid fa-file-pdf me-1"></i> PDF';
        loadingText.textContent = 'Đang tải tài liệu PDF trực tiếp...';

        let iframeLoaded = false;
        pdfIframe.onload = function() {
            iframeLoaded = true;
            loadingEl.classList.add('d-none');
            pdfContainer.classList.remove('d-none');
        };

        // Fallback ẩn loading sau 600ms nếu trình duyệt nhúng plugin PDF không bắn sự kiện onload
        setTimeout(function() {
            if (!iframeLoaded) {
                loadingEl.classList.add('d-none');
                pdfContainer.classList.remove('d-none');
            }
        }, 800);

        pdfIframe.src = fileUrl;
    } else if (isDocx) {
        badgeEl.className = 'badge bg-primary text-white fw-bold px-2 py-1';
        badgeEl.innerHTML = '<i class="fa-solid fa-file-word me-1"></i> WORD (.DOCX)';
        loadingText.textContent = 'Đang đọc và chuyển đổi tài liệu Word...';

        if (typeof mammoth === 'undefined') {
            loadingEl.classList.add('d-none');
            fallbackContainer.classList.remove('d-none');
            fallbackMsg.textContent = 'Trình đọc file Word chưa tải xong. Bạn vui lòng mở file trong tab mới hoặc tải về máy để xem.';
            return;
        }

        fetch(fileUrl)
            .then(function(res) {
                if (!res.ok) throw new Error('Không thể tải tệp từ máy chủ (Mã: ' + res.status + ')');
                return res.arrayBuffer();
            })
            .then(function(arrayBuffer) {
                return mammoth.convertToHtml({ arrayBuffer: arrayBuffer });
            })
            .then(function(result) {
                loadingEl.classList.add('d-none');
                docxContainer.classList.remove('d-none');
                docxContent.innerHTML = result.value || '<div class="alert alert-info text-center">Tệp đề cương này không có nội dung văn bản.</div>';
            })
            .catch(function(err) {
                console.warn('Lỗi đọc nhanh Word:', err);
                loadingEl.classList.add('d-none');
                fallbackContainer.classList.remove('d-none');
                fallbackMsg.textContent = 'Không thể xem trước tệp Word này: ' + (err.message || 'Lỗi đọc tệp') + '. Bạn có thể tải về để mở trên máy.';
            });
    } else {
        badgeEl.className = 'badge bg-secondary text-white fw-bold px-2 py-1';
        badgeEl.innerHTML = '<i class="fa-solid fa-file-lines me-1"></i> TÀI LIỆU';
        loadingEl.classList.add('d-none');
        fallbackContainer.classList.remove('d-none');
        if (isDoc) {
            fallbackMsg.textContent = 'Tệp có định dạng Word cũ (.doc). Trình duyệt hỗ trợ xem nhanh tốt nhất với tệp .pdf và .docx. Bạn có thể mở hoặc tải về máy để xem bằng Word.';
        } else {
            fallbackMsg.textContent = 'Tệp có định dạng đặc biệt. Bạn có thể mở hoặc tải về máy tính để xem đầy đủ.';
        }
    }
}

// Xóa nguồn iframe khi đóng modal để giải phóng tài nguyên
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('modalQuickPreviewDeCuong');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            const pdfIframe = document.getElementById('quickPreviewIframe');
            if (pdfIframe) pdfIframe.src = '';
            const docxContent = document.getElementById('quickPreviewDocxContent');
            if (docxContent) docxContent.innerHTML = '';
        });
    }
});
</script>

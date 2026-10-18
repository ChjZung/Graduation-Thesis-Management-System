{{-- ========================================================
   HUIT - MODAL XEM NHANH ĐỀ CƯƠNG CHI TIẾT (.PDF / .DOCX)
   Sử dụng: @include('partials.modal_preview_decuong')
   Gọi JS: quickPreviewOutline(fileUrl, fileName, topicTitle)
======================================================== --}}

@once('modal_preview_decuong')
<div class="modal fade" id="modalQuickPreviewDeCuong" tabindex="-1" aria-labelledby="modalQuickPreviewLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw; height: 92vh; margin: 4vh auto;">
        <div class="modal-content h-100 border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
            
            <!-- Modal Header -->
            <div class="modal-header py-3 px-4 text-white" style="background-color: #00529b; flex-shrink: 0;">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <span class="badge bg-white text-primary fw-bold px-2 py-1" id="quickPreviewTypeBadge" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-file me-2"></i> ĐỀ CƯƠNG
                    </span>
                    <h6 class="modal-title text-truncate fw-bold mb-0 text-white" id="modalQuickPreviewLabel" style="font-size: 1rem;" title="Xem nhanh đề cương">
                        Xem Nhanh Đề Cương Chi Tiết
                    </h6>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0">
                    <a href="#" id="quickPreviewOpenTabBtn" target="_blank" class="btn btn-sm btn-outline-light py-1 px-3 rounded-pill d-inline-flex align-items-center" title="Mở trong tab mới của trình duyệt">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> <span class="d-none d-sm-inline">Mở tab mới</span>
                    </a>
                    <a href="#" id="quickPreviewDownloadBtn" download class="btn btn-sm btn-warning text-dark fw-semibold py-1 px-3 rounded-pill d-inline-flex align-items-center" title="Tải file về máy tính">
                        <i class="fa-solid fa-download me-2"></i> <span class="d-none d-sm-inline">Tải về</span>
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

                <!-- PDF Preview Container (Mozilla PDF.js Canvas Renderer) -->
                <div id="quickPreviewPdfContainer" class="w-100 h-100 d-none d-flex flex-column" style="position: absolute; top:0; left:0; right:0; bottom:0;">
                    <!-- PDF Toolbar -->
                    <div class="bg-dark text-white px-3 py-2 border-bottom d-flex justify-content-between align-items-center flex-shrink-0" style="font-size: 13px; z-index: 10;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger text-white"><i class="fa-solid fa-file-pdf me-2"></i> PDF</span>
                            <span class="text-white-50">|</span>
                            <span class="text-light">
                                Trang <span id="pdfCurrentPage" class="fw-bold text-white">1</span> / <span id="pdfTotalPages" class="fw-bold text-white">1</span>
                            </span>
                            <div class="btn-group btn-group-sm ms-2">
                                <button type="button" class="btn btn-outline-light btn-sm py-0 px-2" id="pdfPrevPageBtn" title="Trang trước">
                                    <i class="fa-solid fa-chevron-up"></i>
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm py-0 px-2" id="pdfNextPageBtn" title="Trang tiếp">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2">
                            <div class="btn-group btn-group-sm me-2">
                                <button type="button" class="btn btn-outline-light btn-sm py-0 px-2" id="pdfZoomOutBtn" title="Thu nhỏ">
                                    <i class="fa-solid fa-magnifying-glass-minus"></i>
                                </button>
                                <span class="btn btn-outline-light btn-sm py-0 px-2 disabled text-white fw-bold" id="pdfZoomLevel" style="min-width: 55px;">100%</span>
                                <button type="button" class="btn btn-outline-light btn-sm py-0 px-2" id="pdfZoomInBtn" title="Phóng to">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm py-0 px-2" id="pdfFitWidthBtn" title="Vừa chiều ngang">
                                    <i class="fa-solid fa-arrows-left-right-to-line"></i>
                                </button>
                            </div>
                            
                            <a href="#" id="quickPreviewPdfOpenDirect" target="_blank" class="btn btn-sm btn-outline-info py-1 px-3 rounded-pill d-inline-flex align-items-center" style="font-size: 12px;">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Mở tab riêng
                            </a>
                            <a href="#" id="quickPreviewPdfDownloadDirect" download class="btn btn-sm btn-warning text-dark fw-bold py-1 px-3 rounded-pill d-inline-flex align-items-center" style="font-size: 12px;">
                                <i class="fa-solid fa-download me-2"></i> Tải PDF
                            </a>
                        </div>
                    </div>

                    <!-- PDF Canvas Scroll Area -->
                    <div id="pdfViewerWrapper" class="flex-grow-1 overflow-auto text-center p-3" style="background-color: #525659; min-height: 0;">
                        <div id="pdfPagesContainer" class="d-flex flex-column align-items-center gap-3">
                            <!-- Pages will be rendered here as HTML5 canvases -->
                        </div>
                        <!-- Fallback iframe container -->
                        <div id="pdfIframeFallback" class="w-100 h-100 d-none" style="min-height: 500px;">
                            <iframe id="quickPreviewIframe" src="" style="width: 100%; height: 100%; min-height: 550px; border: none;"></iframe>
                        </div>
                    </div>
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
                            <a href="#" id="quickPreviewFallbackOpenBtn" target="_blank" class="btn btn-outline-primary px-3 rounded-pill d-inline-flex align-items-center">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Mở tab mới
                            </a>
                            <a href="#" id="quickPreviewFallbackDownloadBtn" download class="btn btn-primary px-3 rounded-pill d-inline-flex align-items-center">
                                <i class="fa-solid fa-download me-2"></i> Tải về máy
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer (Small status line) -->
            <div class="modal-footer py-1 px-3 bg-white border-top d-flex justify-content-between align-items-center" style="flex-shrink: 0;">
                <div class="small text-muted" id="quickPreviewFooterInfo">
                    <i class="fa-regular fa-circle-question me-2 text-primary"></i> Nhấn phím <kbd>ESC</kbd> hoặc nút đóng để quay lại màn hình xét duyệt.
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
.pdf-page-box {
    box-shadow: 0 4px 14px rgba(0,0,0,0.35);
    background-color: #ffffff;
    border-radius: 4px;
    overflow: hidden;
    transition: transform 0.15s ease;
}
.pdf-page-box canvas {
    display: block;
    margin: 0 auto;
}
</style>

<!-- Nạp thư viện Mammoth.js (Word .docx) và Mozilla PDF.js (PDF canvas) -->
<script src="{{ asset('js/mammoth.browser.min.js') }}"></script>
<script src="{{ asset('js/pdf.min.js') }}"></script>

<script>
let currentPdfDoc = null;
let currentPdfScale = 'fit';
let currentPdfNumPages = 0;
let isRenderingPdf = false;

// Cấu hình worker cho PDF.js
if (window.pdfjsLib) {
    window.pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
}

/**
 * Dựng toàn bộ các trang PDF thành Canvas chất lượng cao
 */
function renderAllPdfPages(pdfDoc, scale) {
    const pagesContainer = document.getElementById('pdfPagesContainer');
    if (!pagesContainer) return;
    pagesContainer.innerHTML = '';

    const wrapper = document.getElementById('pdfViewerWrapper');
    const availableWidth = wrapper ? Math.max(wrapper.clientWidth - 56, 500) : 850;
    const zoomEl = document.getElementById('pdfZoomLevel');
    const dpr = window.devicePixelRatio || 1;

    for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
        const pageDiv = document.createElement('div');
        pageDiv.className = 'pdf-page-box';
        pageDiv.id = 'pdf-page-' + pageNum;
        pageDiv.dataset.page = pageNum;

        const canvas = document.createElement('canvas');
        pageDiv.appendChild(canvas);
        pagesContainer.appendChild(pageDiv);

        (function(num, c, div) {
            pdfDoc.getPage(num).then(function(page) {
                let actualScale = scale;
                if (scale === 'fit' || !scale) {
                    const unscaledViewport = page.getViewport({ scale: 1.0 });
                    actualScale = Math.min(Math.max(availableWidth / unscaledViewport.width, 0.6), 2.2);
                    if (zoomEl) zoomEl.textContent = Math.round(actualScale * 100) + '%';
                } else if (zoomEl) {
                    zoomEl.textContent = Math.round(scale * 100) + '%';
                }

                const viewport = page.getViewport({ scale: actualScale });
                c.style.width = viewport.width + 'px';
                c.style.height = viewport.height + 'px';

                c.width = Math.floor(viewport.width * dpr);
                c.height = Math.floor(viewport.height * dpr);

                const ctx = c.getContext('2d');
                ctx.scale(dpr, dpr);

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                page.render(renderContext);
            });
        })(pageNum, canvas, pageDiv);
    }
}

/**
 * Hiển thị fallback sang iframe khi PDF.js không khởi tạo được
 */
function showPdfIframeFallback(url) {
    const pagesContainer = document.getElementById('pdfPagesContainer');
    const iframeFallback = document.getElementById('pdfIframeFallback');
    const pdfIframe = document.getElementById('quickPreviewIframe');
    const loadingEl = document.getElementById('quickPreviewLoading');
    const pdfContainer = document.getElementById('quickPreviewPdfContainer');

    if (pagesContainer) pagesContainer.innerHTML = '';
    if (iframeFallback) iframeFallback.classList.remove('d-none');
    if (pdfIframe) pdfIframe.src = url;

    loadingEl.classList.add('d-none');
    pdfContainer.classList.remove('d-none');
}

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
    const pagesContainer = document.getElementById('pdfPagesContainer');
    const iframeFallback = document.getElementById('pdfIframeFallback');

    // 1. Chuẩn hóa fileUrl: Nếu là full URL có cùng host hoặc localhost/127.0.0.1, chuyển về tương đối
    let targetUrl = fileUrl;
    try {
        if (fileUrl.startsWith('http://') || fileUrl.startsWith('https://')) {
            const urlObj = new URL(fileUrl, window.location.origin);
            if (urlObj.hostname === window.location.hostname || 
               (urlObj.hostname.match(/^(localhost|127\.0\.0\.1)$/) && window.location.hostname.match(/^(localhost|127\.0\.0\.1)$/))) {
                targetUrl = urlObj.pathname + urlObj.search;
            }
        } else if (!fileUrl.startsWith('/')) {
            targetUrl = '/' + fileUrl;
        }
    } catch (e) {
        targetUrl = fileUrl;
    }

    const safeName = fileName || 'De_Cuong_Chi_Tiet';

    // Cập nhật các liên kết mở tab và tải về
    openTabBtn.href = targetUrl;
    downloadBtn.href = targetUrl;
    downloadBtn.setAttribute('download', safeName);
    fallbackOpenBtn.href = targetUrl;
    fallbackDownloadBtn.href = targetUrl;
    fallbackDownloadBtn.setAttribute('download', safeName);

    const directOpenPdf = document.getElementById('quickPreviewPdfOpenDirect');
    const directDownloadPdf = document.getElementById('quickPreviewPdfDownloadDirect');
    if (directOpenPdf) directOpenPdf.href = targetUrl;
    if (directDownloadPdf) {
        directDownloadPdf.href = targetUrl;
        directDownloadPdf.setAttribute('download', safeName);
    }

    // Tiêu đề modal
    titleEl.textContent = topicTitle ? ('Đề cương: ' + topicTitle) : ('Xem nhanh: ' + safeName);
    titleEl.title = titleEl.textContent;

    // Reset giao diện trước khi tải
    loadingEl.classList.remove('d-none');
    pdfContainer.classList.add('d-none');
    docxContainer.classList.add('d-none');
    fallbackContainer.classList.add('d-none');
    if (pagesContainer) pagesContainer.innerHTML = '';
    if (iframeFallback) iframeFallback.classList.add('d-none');
    if (pdfIframe) pdfIframe.src = '';
    if (docxContent) docxContent.innerHTML = '';

    // Nhận diện phần mở rộng file
    const cleanUrl = targetUrl.split('?')[0].toLowerCase();
    const isPdf = cleanUrl.endsWith('.pdf');
    const isDocx = cleanUrl.endsWith('.docx');
    const isDoc = cleanUrl.endsWith('.doc');

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    if (isPdf) {
        badgeEl.className = 'badge bg-danger text-white fw-bold px-2 py-1';
        badgeEl.innerHTML = '<i class="fa-solid fa-file-pdf me-2"></i> PDF';
        loadingText.textContent = 'Đang đọc và dựng tài liệu PDF trực tiếp...';

        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
            pdfjsLib.getDocument(targetUrl).promise.then(function(pdfDoc) {
                currentPdfDoc = pdfDoc;
                currentPdfNumPages = pdfDoc.numPages;
                currentPdfScale = 'fit';

                document.getElementById('pdfTotalPages').textContent = currentPdfNumPages;
                document.getElementById('pdfCurrentPage').textContent = '1';

                loadingEl.classList.add('d-none');
                pdfContainer.classList.remove('d-none');

                renderAllPdfPages(pdfDoc, 'fit');
            }).catch(function(err) {
                console.warn('Lỗi đọc PDF bằng PDF.js:', err);
                showPdfIframeFallback(targetUrl);
            });
        } else {
            showPdfIframeFallback(targetUrl);
        }
    } else if (isDocx) {
        badgeEl.className = 'badge bg-primary text-white fw-bold px-2 py-1';
        badgeEl.innerHTML = '<i class="fa-solid fa-file-word me-2"></i> WORD (.DOCX)';
        loadingText.textContent = 'Đang đọc và chuyển đổi tài liệu Word...';

        if (typeof mammoth === 'undefined') {
            loadingEl.classList.add('d-none');
            fallbackContainer.classList.remove('d-none');
            fallbackMsg.textContent = 'Trình đọc file Word chưa tải xong. Bạn vui lòng mở file trong tab mới hoặc tải về máy để xem.';
            return;
        }

        fetch(targetUrl)
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
        badgeEl.innerHTML = '<i class="fa-solid fa-file-lines me-2"></i> TÀI LIỆU';
        loadingEl.classList.add('d-none');
        fallbackContainer.classList.remove('d-none');
        if (isDoc) {
            fallbackMsg.textContent = 'Tệp có định dạng Word cũ (.doc). Trình duyệt hỗ trợ xem nhanh tốt nhất với tệp .pdf và .docx. Bạn có thể mở hoặc tải về máy để xem bằng Word.';
        } else {
            fallbackMsg.textContent = 'Tệp có định dạng đặc biệt. Bạn có thể mở hoặc tải về máy tính để xem đầy đủ.';
        }
    }
}

// Thiết lập các sự kiện điều khiển PDF khi trang sẵn sàng
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('modalQuickPreviewDeCuong');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            const pdfIframe = document.getElementById('quickPreviewIframe');
            if (pdfIframe) pdfIframe.src = '';
            const docxContent = document.getElementById('quickPreviewDocxContent');
            if (docxContent) docxContent.innerHTML = '';
            const pagesContainer = document.getElementById('pdfPagesContainer');
            if (pagesContainer) pagesContainer.innerHTML = '';
            currentPdfDoc = null;
        });

        // Tự động căn chỉnh trang khi modal được mở hoàn toàn
        modalEl.addEventListener('shown.bs.modal', function() {
            if (currentPdfDoc) {
                renderAllPdfPages(currentPdfDoc, currentPdfScale);
            }
        });
    }

    // Nút Zoom In
    const zoomInBtn = document.getElementById('pdfZoomInBtn');
    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', function() {
            if (!currentPdfDoc) return;
            const curScale = (currentPdfScale === 'fit' || typeof currentPdfScale !== 'number') ? 1.0 : currentPdfScale;
            currentPdfScale = Math.min(curScale + 0.2, 2.5);
            renderAllPdfPages(currentPdfDoc, currentPdfScale);
        });
    }

    // Nút Zoom Out
    const zoomOutBtn = document.getElementById('pdfZoomOutBtn');
    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', function() {
            if (!currentPdfDoc) return;
            const curScale = (currentPdfScale === 'fit' || typeof currentPdfScale !== 'number') ? 1.0 : currentPdfScale;
            currentPdfScale = Math.max(curScale - 0.2, 0.5);
            renderAllPdfPages(currentPdfDoc, currentPdfScale);
        });
    }

    // Nút Fit Width
    const fitBtn = document.getElementById('pdfFitWidthBtn');
    if (fitBtn) {
        fitBtn.addEventListener('click', function() {
            if (!currentPdfDoc) return;
            currentPdfScale = 'fit';
            renderAllPdfPages(currentPdfDoc, 'fit');
        });
    }

    // Cuộn trang trước / sau
    const prevBtn = document.getElementById('pdfPrevPageBtn');
    const nextBtn = document.getElementById('pdfNextPageBtn');
    const wrapper = document.getElementById('pdfViewerWrapper');

    if (prevBtn && wrapper) {
        prevBtn.addEventListener('click', function() {
            const cur = parseInt(document.getElementById('pdfCurrentPage').textContent) || 1;
            if (cur > 1) {
                const targetEl = document.getElementById('pdf-page-' + (cur - 1));
                if (targetEl) {
                    wrapper.scrollTo({ top: targetEl.offsetTop - 15, behavior: 'smooth' });
                    document.getElementById('pdfCurrentPage').textContent = cur - 1;
                }
            }
        });
    }

    if (nextBtn && wrapper) {
        nextBtn.addEventListener('click', function() {
            const cur = parseInt(document.getElementById('pdfCurrentPage').textContent) || 1;
            const total = parseInt(document.getElementById('pdfTotalPages').textContent) || 1;
            if (cur < total) {
                const targetEl = document.getElementById('pdf-page-' + (cur + 1));
                if (targetEl) {
                    wrapper.scrollTo({ top: targetEl.offsetTop - 15, behavior: 'smooth' });
                    document.getElementById('pdfCurrentPage').textContent = cur + 1;
                }
            }
        });
    }

    // Cập nhật số trang hiện tại khi cuộn
    if (wrapper) {
        wrapper.addEventListener('scroll', function() {
            const pageBoxes = document.querySelectorAll('.pdf-page-box');
            if (!pageBoxes.length) return;
            const wrapperTop = wrapper.scrollTop;
            let activePage = 1;

            pageBoxes.forEach(function(box) {
                if (box.offsetTop - wrapper.offsetTop <= wrapperTop + 100) {
                    activePage = box.dataset.page;
                }
            });
            const curPageEl = document.getElementById('pdfCurrentPage');
            if (curPageEl) curPageEl.textContent = activePage;
        });
    }
});
</script>
@endonce

{{-- ========================================================
   HUIT - TRÌNH SOẠN THẢO VĂN BẢN CÓ ĐỊNH DẠNG (RICH TEXT EDITOR)
   Thành phần chuẩn dùng chung cho tất cả các trường nhập nội dung dài trong hệ thống
   Sử dụng: @include('partials.rich_text_editor', [
       'name'         => 'YeuCau',
       'id'           => 'YeuCau',
       'value'        => old('YeuCau', ...),
       'label'        => 'Yêu cầu đối với sinh viên thực hiện',
       'placeholder'  => 'Nhập yêu cầu...',
       'required'     => false,
       'minHeight'    => '180px',
       'showTemplate' => false,
   ])
======================================================== --}}

@php
    $inputName = $name ?? 'MoTa';
    $inputId = $id ?? $inputName;
    $initialValue = $value ?? '';
    $cleanInitial = \App\Helpers\HtmlHelper::clean($initialValue);
    $fieldLabel = $label ?? 'Mô Tả Đề Tài & Mục Tiêu Nghiên Cứu';
    $isRequired = $required ?? false;
    $editorHelp = $helpText ?? ($inputName === 'MoTa' ? 'Trình bày mục tiêu, nội dung thực hiện và yêu cầu của đề tài. Sử dụng thanh công cụ để định dạng văn bản.' : null);
    $editorPlaceholder = $placeholder ?? 'Nhập nội dung chi tiết...';
    $editorMinHeight = $minHeight ?? (($compact ?? false) ? '140px' : '220px');
    $showSuggest = $showTemplate ?? ($inputName === 'MoTa');
    $minCharLimit = $minChars ?? ($inputName === 'MoTa' ? 30 : 0);
    $fnSuffix = preg_replace('/[^a-zA-Z0-9_]/', '_', $inputId);
    $customTemplate = $templateHtml ?? null;
@endphp

<div class="rich-text-editor-container mb-3" id="container-{{ $inputId }}">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1 gap-2">
        <label for="{{ $inputId }}" class="form-label fw-bold mb-0 text-dark">
            {{ $fieldLabel }}
            @if($isRequired)
                <span class="text-danger">*</span>
            @endif
        </label>
        @if($showSuggest)
        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3 shadow-none d-inline-flex align-items-center" id="btn-insert-template-{{ $inputId }}" style="font-size: 12.5px;">
            <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i> Chèn mẫu gợi ý
        </button>
        @endif
    </div>

    @if($editorHelp)
    <!-- Hướng dẫn ngắn -->
    <div class="text-muted small mb-2 d-flex align-items-center gap-2" style="font-size: 13px;">
        <i class="fa-solid fa-circle-info text-primary me-2"></i>
        <span>{{ $editorHelp }}</span>
    </div>
    @endif

    <!-- Khung chính của Rich Text Editor -->
    <div class="rte-wrapper card border rounded-3 overflow-hidden shadow-xs @error($inputName) border-danger @enderror" id="rte-card-{{ $inputId }}">
        <!-- THANH CÔNG CỤ (TOOLBAR) -->
        <div class="rte-toolbar bg-light border-bottom p-2 d-flex flex-wrap align-items-center gap-1" role="toolbar" aria-label="Thanh công cụ định dạng văn bản">
            
            <!-- Nhóm 1: Kiểu chữ (Bold, Italic, Underline) -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="bold" title="In đậm (Ctrl+B)">
                    <i class="fa-solid fa-bold fw-bold"></i>
                </button>
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="italic" title="In nghiêng (Ctrl+I)">
                    <i class="fa-solid fa-italic"></i>
                </button>
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="underline" title="Gạch chân (Ctrl+U)">
                    <i class="fa-solid fa-underline"></i>
                </button>
            </div>

            <div class="vr my-1 mx-1 text-secondary opacity-25"></div>

            <!-- Nhóm 2: Danh sách (Bullet list, Numbered list) -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="insertUnorderedList" title="Danh sách gạch đầu dòng">
                    <i class="fa-solid fa-list-ul"></i>
                </button>
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="insertOrderedList" title="Danh sách đánh số thứ tự">
                    <i class="fa-solid fa-list-ol"></i>
                </button>
            </div>

            <div class="vr my-1 mx-1 text-secondary opacity-25"></div>

            <!-- Nhóm 3: Căn chỉnh lề (Left, Center, Right) -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="justifyLeft" title="Căn lề trái">
                    <i class="fa-solid fa-align-left"></i>
                </button>
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="justifyCenter" title="Căn giữa">
                    <i class="fa-solid fa-align-center"></i>
                </button>
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="justifyRight" title="Căn lề phải">
                    <i class="fa-solid fa-align-right"></i>
                </button>
            </div>

            <div class="vr my-1 mx-1 text-secondary opacity-25"></div>

            <!-- Nhóm 4: Tạo đoạn & Xóa định dạng -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="formatBlock" data-value="p" title="Tạo đoạn văn bản (Paragraph)">
                    <i class="fa-solid fa-paragraph"></i>
                </button>
                <button type="button" class="btn btn-light border-0 rte-btn text-danger" data-command="removeFormat" title="Xóa định dạng">
                    <i class="fa-solid fa-eraser"></i>
                </button>
            </div>

            <div class="vr my-1 mx-1 text-secondary opacity-25"></div>

            <!-- Nhóm 5: Hoàn tác / Làm lại (Undo, Redo) -->
            <div class="btn-group btn-group-sm ms-auto" role="group">
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="undo" title="Hoàn tác (Ctrl+Z)">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
                <button type="button" class="btn btn-light border-0 rte-btn" data-command="redo" title="Làm lại (Ctrl+Y)">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>
        </div>

        <!-- VÙNG SOẠN THẢO TRỰC QUAN (WYSIWYG CONTENTEDITABLE) -->
        <div class="rte-editor p-3 text-dark bg-white" 
             id="rte-editor-{{ $inputId }}" 
             contenteditable="true" 
             role="textbox" 
             aria-multiline="true"
             style="min-height: {{ $editorMinHeight }};"
             data-placeholder="{{ $editorPlaceholder }}">{!! $cleanInitial !!}</div>

        <!-- Thanh trạng thái chân trang (Footer bar) -->
        <div class="rte-footer bg-light px-3 py-2 border-top d-flex justify-content-between align-items-center text-muted" style="font-size: 12.5px;">
            <div>
                <span id="rte-counter-{{ $inputId }}" class="fw-semibold text-secondary">0</span> ký tự văn bản
                @if($minCharLimit > 0)
                <span id="rte-hint-{{ $inputId }}" class="ms-1 text-danger d-none">(Cần tối thiểu {{ $minCharLimit }} ký tự)</span>
                @endif
            </div>
            <div class="small">
                <i class="fa-solid fa-shield-halved text-success me-2"></i>Bảo vệ an toàn dữ liệu HTML
            </div>
        </div>
    </div>

    <!-- TEXTAREA ẨN ĐỒNG BỘ DỮ LIỆU ĐỂ SUBMIT FORM LARAVEL CHUẨN XÁC -->
    <textarea name="{{ $inputName }}" id="{{ $inputId }}" class="d-none" {{ $isRequired ? 'required' : '' }}>{{ $cleanInitial }}</textarea>

    @error($inputName)
        <div class="invalid-feedback d-block fw-semibold mt-1" style="font-size: 13px;">{{ $message }}</div>
    @enderror
</div>

<style>
    /* Styling thanh công cụ và vùng soạn thảo Rich Text Editor */
    .rich-text-editor-container {
        position: relative;
        clear: both;
        margin-bottom: 1.5rem !important;
    }
    .rich-text-editor-container .rte-wrapper {
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
        background-color: #ffffff;
        position: relative;
        z-index: 1;
    }
    .rich-text-editor-container .rte-wrapper.focus-within,
    .rich-text-editor-container .rte-wrapper:focus-within {
        border-color: #00838C !important;
        box-shadow: 0 0 0 3px rgba(0, 131, 140, 0.16) !important;
        z-index: 2;
    }
    .rich-text-editor-container .rte-toolbar {
        background-color: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        user-select: none;
    }
    .rich-text-editor-container .rte-btn {
        width: 34px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px !important;
        color: #334155;
        font-size: 13.5px;
        transition: all 0.15s ease;
        background: transparent;
    }
    .rich-text-editor-container .rte-btn:hover {
        background-color: #e2e8f0 !important;
        color: #004E64 !important;
        transform: translateY(-1px);
    }
    .rich-text-editor-container .rte-btn:active,
    .rich-text-editor-container .rte-btn.active {
        background-color: #e6f7fa !important;
        color: #00838C !important;
        border: 1px solid #bce1e8 !important;
        font-weight: 700;
    }
    .rich-text-editor-container .rte-editor {
        min-height: {{ $editorMinHeight }};
        font-size: 14px;
        line-height: 1.65;
        color: #1e293b;
        outline: none;
        word-break: break-word;
        padding: 16px 18px !important;
        height: auto;
        overflow: visible;
    }
    .rich-text-editor-container .rte-editor img {
        max-width: 100% !important;
        height: auto !important;
        border-radius: 6px;
        display: inline-block;
        margin: 8px 0;
    }
    /* Typography bên trong vùng soạn thảo */
    .rich-text-editor-container .rte-editor p {
        margin-bottom: 0.85rem;
    }
    .rich-text-editor-container .rte-editor p:last-child {
        margin-bottom: 0;
    }
    .rich-text-editor-container .rte-editor strong,
    .rich-text-editor-container .rte-editor b {
        color: #003F5F;
        font-weight: 700;
    }
    .rich-text-editor-container .rte-editor ul,
    .rich-text-editor-container .rte-editor ol {
        margin-bottom: 0.85rem;
        padding-left: 2rem;
    }
    .rich-text-editor-container .rte-editor li {
        margin-bottom: 0.35rem;
        line-height: 1.55;
    }
    .rich-text-editor-container .rte-editor u {
        text-decoration-thickness: 1.5px;
        text-underline-offset: 2px;
    }
    /* Placeholder khi vùng soạn thảo trống */
    .rich-text-editor-container .rte-editor:empty:before,
    .rich-text-editor-container .rte-editor[data-empty="true"]:before {
        content: attr(data-placeholder);
        color: #94a3b8;
        font-style: italic;
        pointer-events: none;
        display: block;
    }
</style>

<script>
(function () {
    function initEditor_{{ $fnSuffix }}() {
        const editorId = '{{ $inputId }}';
        const editorEl = document.getElementById('rte-editor-' + editorId);
        const hiddenTextarea = document.getElementById(editorId);
        const counterEl = document.getElementById('rte-counter-' + editorId);
        const hintEl = document.getElementById('rte-hint-' + editorId);
        const container = document.getElementById('container-' + editorId);
        const btnTemplate = document.getElementById('btn-insert-template-' + editorId);
        const minChars = {{ (int)$minCharLimit }};

        if (!editorEl || !hiddenTextarea) return;
        if (editorEl.dataset.rteInitialized === 'true') return;
        editorEl.dataset.rteInitialized = 'true';

        // 0. Dọn sạch cấu trúc lồng nhau và loại bỏ hoàn toàn thẻ img nếu có trong dữ liệu
        function cleanNestedEditors() {
            const nestedWrappers = editorEl.querySelectorAll('.rich-text-editor-container, .rte-wrapper, .rte-toolbar, .rte-footer');
            if (nestedWrappers.length > 0) {
                const innerEditor = editorEl.querySelector('.rte-editor');
                if (innerEditor) {
                    editorEl.innerHTML = innerEditor.innerHTML;
                } else {
                    nestedWrappers.forEach(function (w) {
                        w.remove();
                    });
                }
            }
            // Không cho phép hình ảnh tồn tại trong editor
            const imgs = editorEl.querySelectorAll('img');
            if (imgs.length > 0) {
                imgs.forEach(function (img) {
                    img.remove();
                });
            }
        }
        cleanNestedEditors();

        // 1. Kiểm tra trạng thái rỗng để kích hoạt placeholder
        function checkEmptyState() {
            const text = editorEl.innerText.trim();
            const html = editorEl.innerHTML.trim();
            const isEmpty = text === '' && (html === '' || html === '<p><br></p>' || html === '<br>' || html === '<p></p>');
            if (isEmpty) {
                editorEl.setAttribute('data-empty', 'true');
            } else {
                editorEl.removeAttribute('data-empty');
            }

            // Cập nhật đếm ký tự văn bản thuần
            const charLen = text.length;
            if (counterEl) counterEl.textContent = charLen;
            if (hintEl && minChars > 0) {
                if (charLen > 0 && charLen < minChars) {
                    hintEl.classList.remove('d-none');
                } else {
                    hintEl.classList.add('d-none');
                }
            }
        }

        // 2. Đồng bộ nội dung HTML từ Editor vào Textarea ẩn
        function syncToTextarea() {
            checkEmptyState();
            if (editorEl.getAttribute('data-empty') === 'true') {
                hiddenTextarea.value = '';
            } else {
                hiddenTextarea.value = editorEl.innerHTML.trim();
            }
        }

        // 3. Khởi tạo trạng thái ban đầu
        checkEmptyState();
        syncToTextarea();

        // 4. Lắng nghe các sự kiện gõ phím / dán trên Editor
        editorEl.addEventListener('input', syncToTextarea);
        editorEl.addEventListener('keyup', syncToTextarea);
        editorEl.addEventListener('blur', syncToTextarea);
        
        // CHẶN HOÀN TOÀN VIỆC PASTE HÌNH ẢNH (Từ clipboard hoặc chụp màn hình)
        editorEl.addEventListener('paste', function (e) {
            const clipboardData = e.clipboardData || window.clipboardData;
            if (clipboardData) {
                // Kiểm tra nếu dán file ảnh trực tiếp (clipboard items)
                if (clipboardData.items) {
                    for (let i = 0; i < clipboardData.items.length; i++) {
                        if (clipboardData.items[i].type.indexOf('image') !== -1) {
                            e.preventDefault();
                            alert('Trình soạn thảo chỉ hỗ trợ nội dung văn bản, không cho phép chèn hoặc dán hình ảnh.');
                            return;
                        }
                    }
                }
                // Kiểm tra nếu dán HTML có chứa thẻ <img>
                const pastedHtml = clipboardData.getData('text/html');
                if (pastedHtml && /<img\s[^>]*>/i.test(pastedHtml)) {
                    e.preventDefault();
                    // Loại bỏ hoàn toàn thẻ img, chỉ dán phần text và markup văn bản an toàn
                    const cleaned = pastedHtml.replace(/<img[^>]*>/gi, '');
                    document.execCommand('insertHTML', false, cleaned);
                    alert('Đã tự động loại bỏ hình ảnh. Trình soạn thảo chỉ nhận nội dung văn bản.');
                    setTimeout(function () {
                        cleanNestedEditors();
                        syncToTextarea();
                    }, 20);
                    return;
                }
            }
            setTimeout(function () {
                cleanNestedEditors();
                syncToTextarea();
            }, 20);
        });

        // CHẶN KÉO THẢ (DRAG & DROP) FILE ẢNH VÀO EDITOR
        editorEl.addEventListener('dragover', function (e) {
            e.preventDefault();
        });
        editorEl.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                for (let i = 0; i < dt.files.length; i++) {
                    if (dt.files[i].type.indexOf('image') !== -1) {
                        e.preventDefault();
                        alert('Không cho phép kéo thả hình ảnh vào trình soạn thảo.');
                        return;
                    }
                }
            }
        });

        // 5. Đồng bộ trước khi submit form
        const parentForm = hiddenTextarea.closest('form');
        if (parentForm) {
            parentForm.addEventListener('submit', function () {
                cleanNestedEditors();
                syncToTextarea();
            });
        }

        // 6. Xử lý các nút bấm trên thanh công cụ (Toolbar Actions)
        if (container) {
            const toolbarButtons = container.querySelectorAll('.rte-btn');
            toolbarButtons.forEach(function (btn) {
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault(); // Giữ selection trong vùng soạn thảo
                });

                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    editorEl.focus();

                    const command = this.dataset.command;
                    const value = this.dataset.value || null;

                    if (command) {
                        document.execCommand(command, false, value);
                        syncToTextarea();
                        updateToolbarStates();
                    }
                });
            });

            // Cập nhật trạng thái active của các nút dựa trên con trỏ
            function updateToolbarStates() {
                toolbarButtons.forEach(function (btn) {
                    const cmd = btn.dataset.command;
                    if (['bold', 'italic', 'underline', 'insertUnorderedList', 'insertOrderedList', 'justifyLeft', 'justifyCenter', 'justifyRight'].includes(cmd)) {
                        try {
                            if (document.queryCommandState(cmd)) {
                                btn.classList.add('active');
                            } else {
                                btn.classList.remove('active');
                            }
                        } catch (err) {}
                    }
                });
            }

            document.addEventListener('selectionchange', function () {
                if (document.activeElement === editorEl) {
                    updateToolbarStates();
                }
            });
        }

        // 8. Chức năng: Chèn mẫu gợi ý
        if (btnTemplate) {
            btnTemplate.addEventListener('click', function () {
                const currentText = editorEl.innerText.trim();
                if (currentText.length > 10) {
                    if (!confirm('Vùng soạn thảo đã có nội dung. Bạn có chắc muốn chèn thêm mẫu cấu trúc gợi ý vào không?')) {
                        return;
                    }
                }

                @if(!empty($customTemplate))
                    const templateHtml = `{!! addslashes($customTemplate) !!}`;
                @else
                    const templateHtml = `
<p><strong>1. Mục tiêu đề tài:</strong></p>
<ul>
    <li>Trình bày mục tiêu chính và ý nghĩa thực tiễn của đề tài khóa luận.</li>
    <li>Xác định các kết quả đầu ra cụ thể cần đạt được sau khi hoàn thành.</li>
</ul>
<p><strong>2. Nội dung thực hiện:</strong></p>
<ul>
    <li>Khảo sát hiện trạng, phân tích yêu cầu nghiệp vụ và đặc tả bài toán.</li>
    <li>Thiết kế kiến trúc hệ thống, cơ sở dữ liệu và xây dựng các chức năng.</li>
    <li>Kiểm thử hệ thống, đánh giá hiệu năng và triển khai sản phẩm ứng dụng.</li>
</ul>
<p><strong>3. Yêu cầu đối với sinh viên:</strong></p>
<ul>
    <li>Kiến thức nền tảng chuyên ngành vững vàng và tinh thần chủ động làm việc.</li>
    <li>Kỹ năng công nghệ, ngôn ngữ lập trình và các công cụ dự kiến sử dụng.</li>
</ul>`;
                @endif

                if (currentText.length === 0 || editorEl.getAttribute('data-empty') === 'true') {
                    editorEl.innerHTML = templateHtml.trim();
                } else {
                    editorEl.innerHTML += '<br>' + templateHtml.trim();
                }

                editorEl.focus();
                syncToTextarea();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditor_{{ $fnSuffix }});
    } else {
        initEditor_{{ $fnSuffix }}();
    }
})();
</script>

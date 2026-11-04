<div class="modal fade" id="cloExportModal" tabindex="-1" aria-labelledby="cloExportModalTitle" aria-hidden="true" data-selection-url="{{ route('clo.reports.index') }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="cloExportModalTitle">Xuất báo cáo CLO theo ngành và đợt</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button></div>
            <div class="modal-body" data-clo-export-body></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button></div>
        </div>
    </div>
</div>
<script src="{{ asset('js/clo_export_modal.js') }}?v={{ filemtime(public_path('js/clo_export_modal.js')) }}" defer></script>

<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Shared import helper for all Controllers using ExcelImportService.
 */
trait HandlesExcelImport
{
    /**
     * Run an import service method and return a redirect with result message.
     * Catches any exception and returns a user-friendly error instead of crashing.
     *
     * @param Request $request
     * @param string  $serviceMethod  Method name on ExcelImportService
     * @param array   $extraArgs      Extra arguments passed to the service method
     * @param string  $entityLabel    Human-readable label for logging
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function runImport(
        Request $request,
        string $serviceMethod,
        array $extraArgs = [],
        string $entityLabel = 'Dữ liệu'
    ) {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ], [
            'file.required' => 'Vui lòng chọn file CSV/Excel để import.',
            'file.mimes'    => 'Chỉ chấp nhận file định dạng .xlsx, .xls, .csv.',
        ]);

        try {
            $service = new \App\Services\ExcelImportService();
            $res = call_user_func_array([$service, $serviceMethod], array_merge([$request->file('file')], $extraArgs));

            $hasErrors = ($res['error_count'] > 0);
            $icon = $hasErrors ? 'fa-triangle-exclamation' : 'fa-circle-check';

            $msg = "<div class='d-flex align-items-center mb-1'>"
                 . "<i class='fa-solid {$icon} me-2 fs-5'></i>"
                 . "<div><strong>Kết quả Import {$entityLabel}:</strong> "
                 . "Tổng số bản ghi: <strong>{$res['total_count']}</strong> | "
                 . "Thành công: <span class='badge bg-success px-2 py-1'>{$res['success_count']}</span> | "
                 . "Thất bại: <span class='badge bg-danger px-2 py-1'>{$res['error_count']}</span>"
                 . "</div></div>";

            if ($hasErrors && !empty($res['errors'])) {
                $msg .= "<div class='mt-2 pt-2 border-top border-secondary border-opacity-25'>"
                      . "<strong class='text-dark small d-block mb-1'><i class='fa-solid fa-list-check me-1'></i>Danh sách lỗi phát hiện:</strong>"
                      . "<ul class='mb-1 ps-3 small text-danger'>";
                
                $showCount = 0;
                foreach ($res['errors'] as $err) {
                    $rowNum = $err['row'] ?? '?';
                    $reason = e($err['reason'] ?? 'Lỗi không xác định');
                    $msg .= "<li><strong>Dòng {$rowNum}:</strong> {$reason}</li>";
                    $showCount++;
                    if ($showCount >= 10) {
                        $remaining = count($res['errors']) - 10;
                        if ($remaining > 0) {
                            $msg .= "<li><em>...và còn {$remaining} lỗi khác.</em></li>";
                        }
                        break;
                    }
                }
                $msg .= "</ul></div>";
            }

            if ($hasErrors && !empty($res['error_file'])) {
                $msg .= "<div class='mt-2'>"
                      . "<a href='{$res['error_file']}' target='_blank' "
                      . "class='btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm fw-bold me-2'>"
                      . "<i class='fa-solid fa-file-csv me-1'></i>Tải file báo lỗi chi tiết (.CSV)</a>"
                      . "</div>";
            }

            $alertType = $hasErrors ? ($res['success_count'] > 0 ? 'import_warning' : 'import_danger') : 'import_result';

            return redirect()->back()
                ->with('import_result', $msg)
                ->with('import_alert_type', $alertType)
                ->with($alertType, $msg);
        } catch (\Throwable $e) {
            Log::error("Import {$entityLabel} error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $errHtml = "<div class='d-flex align-items-center mb-1'>"
                     . "<i class='fa-solid fa-triangle-exclamation me-2 fs-5 text-danger'></i>"
                     . "<div><strong>Lỗi Import {$entityLabel}:</strong> " . e($e->getMessage()) . "</div></div>"
                     . "<div class='small text-muted'>Vui lòng kiểm tra lại cấu trúc file (.xlsx, .xls, .csv) theo đúng file mẫu chuẩn và thử lại.</div>";
            return redirect()->back()
                ->with('import_danger', $errHtml)
                ->with('import_result', $errHtml)
                ->withErrors(
                    'Lỗi khi đọc file: ' . $e->getMessage()
                    . '. Vui lòng đảm bảo file đúng định dạng (.xlsx, .xls, .csv) và thử lại.'
                );
        }
    }
}

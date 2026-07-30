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

            $msg = "Import hoàn tất! Tổng dòng: <strong>{$res['total_count']}</strong> | "
                 . "Thành công: <strong class='text-success'>{$res['success_count']}</strong> | "
                 . "Thất bại: <strong class='text-danger'>{$res['error_count']}</strong>.";

            if ($res['error_count'] > 0 && !empty($res['error_file'])) {
                $msg .= " <br><a href='{$res['error_file']}' target='_blank' "
                      . "class='fw-bold text-danger text-decoration-underline'>"
                      . "<i class='fa-solid fa-download me-1'></i>Tải file danh sách lỗi chi tiết tại đây</a>.";
            }

            return redirect()->back()->with('import_result', $msg);
        } catch (\Throwable $e) {
            Log::error("Import {$entityLabel} error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(
                'Lỗi khi đọc file: ' . $e->getMessage()
                . '. Vui lòng đảm bảo file đúng định dạng (.xlsx, .xls, .csv) và thử lại.'
            );
        }
    }
}

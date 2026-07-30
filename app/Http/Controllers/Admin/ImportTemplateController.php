<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExcelTemplateService;

class ImportTemplateController extends Controller
{
    /**
     * Download file mẫu Excel (.xlsx) với định dạng chuẩn và kiểu dáng đẹp.
     * URL: GET /import/template/{type}
     */
    public function downloadTemplate(string $type, ExcelTemplateService $templateService)
    {
        return $templateService->downloadTemplate($type);
    }

    /**
     * Download file CSV nhật ký báo lỗi import.
     * URL: GET /import/error-log/{filename}
     */
    public function downloadErrorLog(string $filename)
    {
        $safeFilename = basename($filename);
        $path = storage_path('app/public/import-errors/' . $safeFilename);

        if (!file_exists($path)) {
            abort(404, 'File nhật ký lỗi không tồn tại hoặc đã bị xóa.');
        }

        return response()->download($path, $safeFilename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

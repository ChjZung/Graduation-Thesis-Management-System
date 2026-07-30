<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * Xử lý lưu trữ file đính kèm hoặc URL link
     * 
     * @param Request $request
     * @param string $fileInputName Tên trường file trong request (ví dụ: 'FileUpLoad')
     * @param string $linkInputName Tên trường link trong request (ví dụ: 'FileBaoCao' hoặc 'LinkFile')
     * @param string $folder Thư mục lưu trên storage (ví dụ: 'baocao', 'sanpham')
     * @return string|null Trả về đường dẫn storage hoặc URL link
     */
    public function handleUploadOrLink(Request $request, string $fileInputName, string $linkInputName, string $folder = 'uploads'): ?string
    {
        // 1. Kiểm tra nếu có file đính kèm tải lên
        if ($request->hasFile($fileInputName) && $request->file($fileInputName)->isValid()) {
            $file = $request->file($fileInputName);
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $path = $file->storeAs("public/{$folder}", $filename);
            
            // Trả về đường dẫn dạng storage relative url
            return Storage::url($path);
        }

        // 2. Nếu không có file, lấy link URL nhập từ form
        if ($request->filled($linkInputName)) {
            return $request->input($linkInputName);
        }

        return null;
    }
}

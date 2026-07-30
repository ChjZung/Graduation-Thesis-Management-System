<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadTaiLieuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_tai_lieu' => 'required|file|mimes:pdf,doc,docx,zip,rar|max:20480', // max 20MB
        ];
    }

    public function messages(): array
    {
        return [
            'file_tai_lieu.required' => 'Vui lòng chọn tệp tài liệu đính kèm.',
            'file_tai_lieu.file'     => 'Tệp tải lên không hợp lệ.',
            'file_tai_lieu.mimes'    => 'Chỉ chấp nhận các định dạng tệp: .pdf, .doc, .docx, .zip, .rar.',
            'file_tai_lieu.max'      => 'Kích thước tệp không được vượt quá 20MB.',
        ];
    }
}

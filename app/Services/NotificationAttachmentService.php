<?php
namespace App\Services;

use App\Models\ThongBao;
use Illuminate\Support\Facades\Storage;

class NotificationAttachmentService
{
    /** Null means no recorded attachment; missing means a recorded file is unavailable. */
    public static function resolve(ThongBao $notice): ?array
    {
        $stored = trim((string) $notice->FileDinhKem);
        if ($stored === '') return null;
        if (preg_match('#^https?://#i', $stored)) {
            $url = parse_url($stored);
            // App-generated absolute /storage URLs should resolve locally as well.
            if (($url['host'] ?? '') !== parse_url(config('app.url'), PHP_URL_HOST)) {
                return ['path' => $stored, 'absolute' => null, 'name' => basename($url['path'] ?? 'Tai_lieu'), 'url' => $stored, 'missing' => false];
            }
            $stored = $url['path'] ?? '';
        }
        $relative = ltrim(str_replace('\\', '/', $stored), '/');
        $relative = preg_replace('#^(?:storage/|public/)#', '', $relative);
        $absolute = false;
        if (!preg_match('#(?:^|/)\.{1,2}(?:/|$)|[\x00-\x1f:]#', $relative)) {
            $root = realpath(Storage::disk('public')->path(''));
            foreach (array_unique([$relative, rawurldecode($relative)]) as $candidate) {
                $file = realpath(Storage::disk('public')->path($candidate));
                if ($root && $file && str_starts_with($file, $root.DIRECTORY_SEPARATOR) && is_file($file)) {
                    $absolute = $file;
                    break;
                }
            }
        }
        $name = basename($relative) ?: 'Tai_lieu';
        return ['path' => $relative, 'absolute' => $absolute ?: null, 'name' => $name, 'missing' => !$absolute,
            'url' => $absolute ? route('files.thongbao.attachment', ['id' => $notice->MaThongBao, 'filename' => $name]) : null];
    }
}

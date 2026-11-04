<?php

namespace App\Services;

use Symfony\Component\Mime\MimeTypeGuesserInterface;

class OfficeDocumentMimeTypeGuesser implements MimeTypeGuesserInterface
{
    public function isGuesserSupported(): bool
    {
        return true;
    }

    public function guessMimeType(string $path): ?string
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return null;
        }
        $header = fread($handle, 4);
        fclose($handle);

        // Check OpenXML formats (ZIP archive header: PK\x03\x04)
        if ($header === "PK\x03\x04" && class_exists('\ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($path) === true) {
                if ($zip->locateName('word/document.xml') !== false || $zip->locateName('word/') !== false) {
                    $zip->close();
                    return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                }
                if ($zip->locateName('xl/workbook.xml') !== false || $zip->locateName('xl/') !== false) {
                    $zip->close();
                    return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                }
                if ($zip->locateName('ppt/presentation.xml') !== false || $zip->locateName('ppt/') !== false) {
                    $zip->close();
                    return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                }
                $zip->close();
            }
        }

        // Check legacy DOC (OLE Compound Document starts with \xD0\xCF\x11\xE0)
        if (str_starts_with($header, "\xD0\xCF\x11\xE0")) {
            return 'application/msword';
        }

        // Check PDF (%PDF)
        if ($header === "%PDF") {
            return 'application/pdf';
        }

        return null;
    }
}

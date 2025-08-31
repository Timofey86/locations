<?php

namespace App\Helper;

use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use Throwable;

class FileHelper
{
    public static function downloadFile(string $url, string $extension = ''): string
    {
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException("Failed to download file. Status code: " . $response->getStatusCode());
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'download_' . time());
            if ($tempFile === false) {
                throw new \RuntimeException('Failed to create temp file.');
            }

            if (!empty($extension)) {
                $tempFileWithExt = $tempFile . '.' . ltrim($extension, '.');
                if (!rename($tempFile, $tempFileWithExt)) {
                    throw new \RuntimeException("Failed to rename temp file.");
                }
                $tempFile = $tempFileWithExt;
            }

            file_put_contents($tempFile, $response->getContent());

            if (!is_file($tempFile) || filesize($tempFile) === 0) {
                unlink($tempFile);
                throw new \RuntimeException("Downloaded file is empty or missing: $url");
            }
        } catch (Throwable $e) {
            throw new RuntimeException("File download failed: " . $e->getMessage(), 0, $e);
        }

        return $tempFile;
    }
}

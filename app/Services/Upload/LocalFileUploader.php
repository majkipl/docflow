<?php

namespace App\Services\Upload;

use Illuminate\Http\UploadedFile;

class LocalFileUploader implements FileUploaderInterface
{
    private string $disk;

    /**
     * @param string|null $disk
     */
    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?? (string) config('filesystems.default', 'local');
    }

    /**
     * @param UploadedFile $file
     * @param string $directory
     * @return string
     */
    public function upload(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, $this->disk);

        if ($path === false) {
            throw new \RuntimeException('Nie udało się zapisać pliku.');
        }

        return $path;
    }
}

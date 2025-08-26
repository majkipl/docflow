<?php

namespace App\Services\Upload;

use Illuminate\Http\UploadedFile;

class LocalFileUploader implements FileUploaderInterface
{
    /**
     * @param UploadedFile $file
     * @param string $directory
     * @return string
     */
    public function upload(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'local');
    }
}

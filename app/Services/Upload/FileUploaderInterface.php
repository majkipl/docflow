<?php

namespace App\Services\Upload;

use Illuminate\Http\UploadedFile;

interface FileUploaderInterface
{
    /**
     * Zapisuje plik i zwraca ścieżkę do niego.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string
     */
    public function upload(UploadedFile $file, string $directory): string;
}

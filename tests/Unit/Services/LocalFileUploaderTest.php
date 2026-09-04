<?php

namespace Tests\Unit\Services;

use App\Services\Upload\LocalFileUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class LocalFileUploaderTest extends TestCase
{
    public function test_upload_saves_file_to_default_disk(): void
    {
        Storage::fake('local');

        $uploader = new LocalFileUploader();
        $file = UploadedFile::fake()->create('document.pdf', 150, 'application/pdf');

        $path = $uploader->upload($file, 'documents');

        $this->assertNotEmpty($path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_upload_saves_file_to_custom_disk_when_provided(): void
    {
        Storage::fake('custom_disk');

        $uploader = new LocalFileUploader('custom_disk');
        $file = UploadedFile::fake()->create('contract.docx', 200, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $path = $uploader->upload($file, 'contracts');

        $this->assertNotEmpty($path);
        Storage::disk('custom_disk')->assertExists($path);
    }

    public function test_upload_throws_runtime_exception_when_file_store_fails(): void
    {
        $mockFile = $this->createMock(UploadedFile::class);
        $mockFile->expects($this->once())
            ->method('store')
            ->with('documents', 'local')
            ->willReturn(false);

        $uploader = new LocalFileUploader('local');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Nie udało się zapisać pliku.');

        $uploader->upload($mockFile, 'documents');
    }
}

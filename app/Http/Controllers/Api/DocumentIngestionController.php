<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Repositories\DocumentRepositoryInterface;
use App\Services\Upload\FileUploaderInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class DocumentIngestionController extends Controller
{
    public function __construct(
        private DocumentRepositoryInterface $repository,
        private FileUploaderInterface       $uploader
    )
    {
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Document::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,png,docx', 'max:20480'],
        ]);

        /** @var User $user */
        $user = $request->user();

        /** @var UploadedFile $file */
        $file = $request->file('file');

        try {
            $path = $this->uploader->upload($file, 'api_documents');

            $document = $this->repository->create([
                'user_id' => $user->id,
                'title' => (string) $validated['title'],
                'file_path' => $path,
                'state' => 'draft',
            ]);

            return response()->json([
                'message' => 'Dokument został pomyślnie zaimportowany.',
                'document_id' => $document->id,
                'status' => 'draft',
            ], 201);

        } catch (Exception $e) {
            Log::error('Błąd podczas importu dokumentu przez API: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Wystąpił błąd podczas wgrywania dokumentu.',
            ], 500);
        }
    }
}

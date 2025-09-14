<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\DocumentRepositoryInterface;
use App\Services\Upload\FileUploaderInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        // Podstawowa autoryzacja Sanctum (wymaga przekazania tokenu w nagłówku Bearer)
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:20480', // np. 20MB dla systemów zewnętrznych
        ]);

        try {
            $path = $this->uploader->upload($request->file('file'), 'api_documents');

            $document = $this->repository->create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'file_path' => $path,
                'state' => 'draft', // Domyślnie startujemy od Draft
            ]);

            return response()->json([
                'message' => 'Dokument został pomyślnie zaimportowany.',
                'document_id' => $document->id,
                'status' => 'draft'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Wystąpił błąd podczas wgrywania dokumentu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

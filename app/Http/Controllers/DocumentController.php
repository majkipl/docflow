<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Repositories\DocumentRepositoryInterface;
use App\Services\Auth\RoleVerificationService;
use App\Services\Upload\FileUploaderInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    /**
     * @param DocumentRepositoryInterface $repository
     * @param RoleVerificationService $roleService
     */
    public function __construct(
        private DocumentRepositoryInterface $repository,
        private RoleVerificationService     $roleService
    )
    {
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // Polimorficzny Dashboard na podstawie roli
        if ($this->roleService->hasRole($user, 'manager')) {
            $documents = $this->repository->getPendingForManager();
            $viewType = 'manager';
        } else {
            $documents = $this->repository->getForEmployee($user->id);
            $viewType = 'employee';
        }

        return Inertia::render('Dashboard', [
            'documents' => $documents,
            'viewType' => $viewType
        ]);
    }

    /**
     * @return Response
     */
    public function create()
    {
        return Inertia::render('Documents/Create');
    }

    /**
     * @param Request $request
     * @param FileUploaderInterface $uploader
     * @return RedirectResponse
     */
    public function store(Request $request, FileUploaderInterface $uploader)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,png,docx|max:10240',
        ]);

        $path = $uploader->upload($request->file('file'), 'documents');

        /** @var User $user */
        $user = $request->user();

        $this->repository->create([
            'user_id' => $user->id, 'title' => $request->title,
            'file_path' => $path,
            'state' => 'draft',
        ]);

        return redirect()->route('dashboard')->with('message', 'Dokument został wgrany i ma status Draft.');
    }

    /**
     * @param Document $document
     * @return Response
     */
    public function show(Document $document)
    {
        // Ładowanie relacji dla widoku szczegółów i osi czasu (Audit Log)
        $document->load(['user', 'auditLogs.user']);

        return Inertia::render('Documents/Show', [
            'document' => $document
        ]);
    }

    // Akcje Maszyny Stanów

    /**
     * @param Document $document
     * @return RedirectResponse
     */
    public function submit(Document $document)
    {
        $document->state->submit();
        return back()->with('message', 'Dokument wysłany do weryfikacji.');
    }

    /**
     * @param Document $document
     * @return RedirectResponse
     */
    public function approve(Document $document)
    {
        $document->state->approve();
        return back()->with('message', 'Dokument został zaakceptowany.');
    }

    /**
     * @param Document $document
     * @return RedirectResponse
     */
    public function reject(Document $document)
    {
        $document->state->reject();
        return back()->with('message', 'Dokument został odrzucony.');
    }
}

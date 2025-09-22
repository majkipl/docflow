<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\User;
use App\Repositories\DocumentRepositoryInterface;
use App\Services\Auth\RoleVerificationService;
use App\Services\Upload\FileUploaderInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
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
        Gate::authorize('viewAny', Document::class);

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
        Gate::authorize('create', Document::class);

        return Inertia::render('Documents/Create');
    }

    /**
     * @param StoreDocumentRequest $request
     * @param FileUploaderInterface $uploader
     * @return RedirectResponse
     */
    public function store(StoreDocumentRequest $request, FileUploaderInterface $uploader)
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');

        $path = $uploader->upload($file, 'documents');

        /** @var User $user */
        $user = $request->user();

        $this->repository->create([
            'user_id' => $user->id,
            'title' => (string) $request->validated('title'),
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
        Gate::authorize('view', $document);

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
        Gate::authorize('submit', $document);

        $document->state->submit();
        return back()->with('message', 'Dokument wysłany do weryfikacji.');
    }

    /**
     * @param Document $document
     * @return RedirectResponse
     */
    public function approve(Document $document)
    {
        Gate::authorize('approve', $document);

        $document->state->approve();
        return back()->with('message', 'Dokument został zaakceptowany.');
    }

    /**
     * @param Document $document
     * @return RedirectResponse
     */
    public function reject(Document $document)
    {
        Gate::authorize('reject', $document);

        $document->state->reject();
        return back()->with('message', 'Dokument został odrzucony.');
    }
}

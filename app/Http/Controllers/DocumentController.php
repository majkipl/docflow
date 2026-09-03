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
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        // Polimorficzny Dashboard na podstawie roli: Manager i Admin widzą kolejkę do akceptacji
        if ($this->roleService->hasAnyRole($user, ['manager', 'admin'])) {
            $documents = $this->repository->getPendingForManager();
            $viewType = 'manager';
        } else {
            $documents = $this->repository->getForEmployee($user->id);
            $viewType = 'employee';
        }

        return Inertia::render('Dashboard', [
            'documents' => $documents,
            'viewType' => $viewType,
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

    /**
     * @param Document $document
     * @return StreamedResponse
     */
    public function download(Document $document): StreamedResponse
    {
        Gate::authorize('view', $document);

        $disk = (string) config('filesystems.default', 'local');

        if (!Storage::disk($disk)->exists($document->file_path)) {
            abort(404, 'Plik załącznika nie został odnaleziony na dysku.');
        }

        return Storage::disk($disk)->download($document->file_path);
    }

    // Akcje Maszyny Stanów

    /**
     * @param Request $request
     * @param Document $document
     * @return RedirectResponse
     */
    public function submit(Request $request, Document $document)
    {
        Gate::authorize('submit', $document);

        $document->state->submit($request->user());
        return back()->with('message', 'Dokument wysłany do weryfikacji.');
    }

    /**
     * @param Request $request
     * @param Document $document
     * @return RedirectResponse
     */
    public function approve(Request $request, Document $document)
    {
        Gate::authorize('approve', $document);

        $document->state->approve($request->user());
        return back()->with('message', 'Dokument został zaakceptowany.');
    }

    /**
     * @param Request $request
     * @param Document $document
     * @return RedirectResponse
     */
    public function reject(Request $request, Document $document)
    {
        Gate::authorize('reject', $document);

        $document->state->reject($request->user());
        return back()->with('message', 'Dokument został odrzucony.');
    }
}

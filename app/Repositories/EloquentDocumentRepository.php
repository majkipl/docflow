<?php

namespace App\Repositories;

use App\Models\Document;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentDocumentRepository implements DocumentRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     * @return Document
     */
    public function create(array $data): Document
    {
        return Document::create($data);
    }

    /**
     * @return LengthAwarePaginator<int, Document>
     */
    public function getPendingForManager(): LengthAwarePaginator
    {
        return Document::with('user')->where('state', 'pending')->latest()->paginate(15);
    }

    /**
     * @param int $userId
     * @return LengthAwarePaginator<int, Document>
     */
    public function getForEmployee(int $userId): LengthAwarePaginator
    {
        return Document::where('user_id', $userId)->latest()->paginate(15);
    }

    /**
     * @param int $id
     * @return Document|null
     */
    public function findById(int $id): ?Document
    {
        return Document::find($id);
    }
}

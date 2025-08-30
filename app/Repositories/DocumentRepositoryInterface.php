<?php

namespace App\Repositories;

use App\Models\Document;
use Illuminate\Pagination\LengthAwarePaginator;

interface DocumentRepositoryInterface
{
    /**
     * @param array $data
     * @return Document
     */
    public function create(array $data): Document;

    /**
     * @return LengthAwarePaginator
     */
    public function getPendingForManager(): LengthAwarePaginator;

    /**
     * @param int $userId
     * @return LengthAwarePaginator
     */
    public function getForEmployee(int $userId): LengthAwarePaginator;

    /**
     * @param int $id
     * @return Document|null
     */
    public function findById(int $id): ?Document;
}

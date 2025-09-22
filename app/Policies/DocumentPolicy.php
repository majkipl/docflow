<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Services\Auth\RoleVerificationService;

class DocumentPolicy
{
    /**
     * @param RoleVerificationService $roleService
     */
    public function __construct(private RoleVerificationService $roleService)
    {
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->roleService->hasAnyRole($user, ['admin', 'manager', 'employee']);
    }

    /**
     * @param User $user
     * @param Document $document
     * @return bool
     */
    public function view(User $user, Document $document): bool
    {
        if ($this->roleService->hasAnyRole($user, ['admin', 'manager'])) {
            return true;
        }

        return $user->id === $document->user_id;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->roleService->hasAnyRole($user, ['admin', 'employee']);
    }

    /**
     * @param User $user
     * @param Document $document
     * @return bool
     */
    public function submit(User $user, Document $document): bool
    {
        $canSubmit = ($user->id === $document->user_id) || $this->roleService->hasRole($user, 'admin');

        return $canSubmit && $document->state->getName() === 'draft';
    }

    /**
     * @param User $user
     * @param Document $document
     * @return bool
     */
    public function approve(User $user, Document $document): bool
    {
        return $this->roleService->hasAnyRole($user, ['admin', 'manager'])
            && $document->state->getName() === 'pending';
    }

    /**
     * @param User $user
     * @param Document $document
     * @return bool
     */
    public function reject(User $user, Document $document): bool
    {
        return $this->roleService->hasAnyRole($user, ['admin', 'manager'])
            && $document->state->getName() === 'pending';
    }
}

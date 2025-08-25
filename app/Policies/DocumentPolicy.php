<?php

namespace App\Policies;

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

    // Pozostałe metody (view, create, update) zostaną uzupełnione po utworzeniu modelu Document.
}

<?php

namespace App\Services\Auth;

use App\Models\User;

class RoleVerificationService
{
    /**
     * Sprawdza, czy użytkownik posiada przynajmniej jedną z wymaganych ról.
     *
     * @param User $user
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(User $user, array $roles): bool
    {
        // Optymalizacja: ładowanie relacji (eager loading) zapobiega problemowi N+1
        // w przypadku wielokrotnego sprawdzania ról w jednym cyklu żądania.
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        return $user->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    /**
     * Sprawdza, czy użytkownik posiada konkretną rolę.
     *
     * @param User $user
     * @param string $role
     * @return bool
     */
    public function hasRole(User $user, string $role): bool
    {
        return $this->hasAnyRole($user, [$role]);
    }
}

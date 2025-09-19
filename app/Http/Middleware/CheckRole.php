<?php

namespace App\Http\Middleware;

use App\Services\Auth\RoleVerificationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * @param RoleVerificationService $roleVerificationService
     */
    public function __construct(private RoleVerificationService $roleVerificationService)
    {
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Użycie array_values normalizuje klucze wymuszając typ array<int, string>
        if (!$request->user() || !$this->roleVerificationService->hasAnyRole($request->user(), array_values($roles))) {
            abort(403, 'Brak wymaganych uprawnień (RBAC).');
        }

        return $next($request);
    }
}

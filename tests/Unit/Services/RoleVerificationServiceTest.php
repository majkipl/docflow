<?php

namespace Tests\Unit\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\Auth\RoleVerificationService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class RoleVerificationServiceTest extends TestCase
{
    private RoleVerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoleVerificationService();
    }

    public function test_has_role_returns_true_when_user_has_specified_role_with_loaded_relation(): void
    {
        $role = new Role();
        $role->name = 'manager';

        $user = new User();
        $user->setRelation('roles', new Collection([$role]));

        $this->assertTrue($this->service->hasRole($user, 'manager'));
        $this->assertFalse($this->service->hasRole($user, 'admin'));
    }

    public function test_has_any_role_returns_true_when_at_least_one_role_matches(): void
    {
        $roleEmployee = new Role();
        $roleEmployee->name = 'employee';

        $user = new User();
        $user->setRelation('roles', new Collection([$roleEmployee]));

        $this->assertTrue($this->service->hasAnyRole($user, ['employee', 'manager']));
        $this->assertTrue($this->service->hasAnyRole($user, ['admin', 'employee']));
        $this->assertFalse($this->service->hasAnyRole($user, ['admin', 'manager']));
    }

    public function test_has_any_role_returns_true_when_user_has_multiple_roles(): void
    {
        $roleManager = new Role();
        $roleManager->name = 'manager';

        $roleAdmin = new Role();
        $roleAdmin->name = 'admin';

        $user = new User();
        $user->setRelation('roles', new Collection([$roleManager, $roleAdmin]));

        $this->assertTrue($this->service->hasRole($user, 'manager'));
        $this->assertTrue($this->service->hasRole($user, 'admin'));
        $this->assertFalse($this->service->hasRole($user, 'employee'));
        $this->assertTrue($this->service->hasAnyRole($user, ['employee', 'admin']));
    }

    public function test_has_role_and_has_any_role_return_false_when_user_has_no_roles(): void
    {
        $user = new User();
        $user->setRelation('roles', new Collection([]));

        $this->assertFalse($this->service->hasRole($user, 'employee'));
        $this->assertFalse($this->service->hasAnyRole($user, ['employee', 'manager', 'admin']));
    }
}

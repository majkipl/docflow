<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentRbacTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup ról i użytkowników
        $employeeRole = Role::create(['name' => 'employee']);
        $managerRole = Role::create(['name' => 'manager']);

        $this->employee = User::factory()->create();
        $this->employee->roles()->attach($employeeRole);

        $this->manager = User::factory()->create();
        $this->manager->roles()->attach($managerRole);
    }

    public function test_employee_cannot_approve_document()
    {
        $document = Document::create([
            'user_id' => $this->employee->id,
            'title' => 'Important Doc',
            'file_path' => 'path.pdf',
            'state' => 'pending',
        ]);

        $response = $this->actingAs($this->employee)
            ->post(route('documents.approve', $document));

        // Employee nie ma roli manager, więc Middleware rzuci 403
        $response->assertStatus(403);
    }

    public function test_manager_can_approve_pending_document()
    {
        $document = Document::create([
            'user_id' => $this->employee->id,
            'title' => 'Important Doc',
            'file_path' => 'path.pdf',
            'state' => 'pending',
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('documents.approve', $document));

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Dokument został zaakceptowany.');

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'state' => 'approved'
        ]);
    }

    public function test_unauthenticated_user_is_redirected_to_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect('/login');
    }
}

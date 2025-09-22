<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $employeeA;
    private User $employeeB;
    private User $manager;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $employeeRole = Role::create(['name' => 'employee']);
        $managerRole = Role::create(['name' => 'manager']);
        $adminRole = Role::create(['name' => 'admin']);

        $this->employeeA = User::factory()->create();
        $this->employeeA->roles()->attach($employeeRole);

        $this->employeeB = User::factory()->create();
        $this->employeeB->roles()->attach($employeeRole);

        $this->manager = User::factory()->create();
        $this->manager->roles()->attach($managerRole);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);
    }

    public function test_employee_can_create_document_with_valid_data(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->employeeA)
            ->post(route('documents.store'), [
                'title' => 'New Employment Contract',
                'file' => UploadedFile::fake()->create('contract.pdf', 300, 'application/pdf'),
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('documents', [
            'user_id' => $this->employeeA->id,
            'title' => 'New Employment Contract',
            'state' => 'draft',
        ]);
    }

    public function test_create_document_fails_validation_for_invalid_file_format(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->employeeA)
            ->post(route('documents.store'), [
                'title' => 'Executable File',
                'file' => UploadedFile::fake()->create('script.exe', 300),
            ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_user_without_create_role_cannot_store_document(): void
    {
        $unauthorizedUser = User::factory()->create();

        $response = $this->actingAs($unauthorizedUser)
            ->post(route('documents.store'), [
                'title' => 'Unauthorized Document',
                'file' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            ]);

        $response->assertStatus(403);
    }

    public function test_employee_cannot_view_another_employees_document(): void
    {
        $document = Document::create([
            'user_id' => $this->employeeA->id,
            'title' => 'Secret Contract A',
            'file_path' => 'docs/a.pdf',
            'state' => 'draft',
        ]);

        $response = $this->actingAs($this->employeeB)
            ->get(route('documents.show', $document));

        $response->assertStatus(403);
    }

    public function test_employee_can_view_own_document(): void
    {
        $document = Document::create([
            'user_id' => $this->employeeA->id,
            'title' => 'My Document',
            'file_path' => 'docs/my.pdf',
            'state' => 'draft',
        ]);

        $response = $this->actingAs($this->employeeA)
            ->get(route('documents.show', $document));

        $response->assertStatus(200);
    }

    public function test_manager_and_admin_can_view_any_document(): void
    {
        $document = Document::create([
            'user_id' => $this->employeeA->id,
            'title' => 'Review Document',
            'file_path' => 'docs/review.pdf',
            'state' => 'pending',
        ]);

        $this->actingAs($this->manager)
            ->get(route('documents.show', $document))
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->get(route('documents.show', $document))
            ->assertStatus(200);
    }

    public function test_employee_cannot_submit_another_employees_document(): void
    {
        $document = Document::create([
            'user_id' => $this->employeeA->id,
            'title' => 'Draft Document A',
            'file_path' => 'docs/a.pdf',
            'state' => 'draft',
        ]);

        $response = $this->actingAs($this->employeeB)
            ->post(route('documents.submit', $document));

        $response->assertStatus(403);
    }

    public function test_employee_can_submit_own_draft_document(): void
    {
        $document = Document::create([
            'user_id' => $this->employeeA->id,
            'title' => 'Draft Document A',
            'file_path' => 'docs/a.pdf',
            'state' => 'draft',
        ]);

        $response = $this->actingAs($this->employeeA)
            ->post(route('documents.submit', $document));

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'state' => 'pending',
        ]);
    }

    public function test_cannot_submit_document_that_is_not_draft(): void
    {
        $document = Document::create([
            'user_id' => $this->employeeA->id,
            'title' => 'Pending Document',
            'file_path' => 'docs/p.pdf',
            'state' => 'pending',
        ]);

        $response = $this->actingAs($this->employeeA)
            ->post(route('documents.submit', $document));

        $response->assertStatus(403);
    }

    public function test_manager_cannot_approve_draft_document(): void
    {
        $document = Document::create([
            'user_id' => $this->employeeA->id,
            'title' => 'Draft Document',
            'file_path' => 'docs/d.pdf',
            'state' => 'draft',
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('documents.approve', $document));

        $response->assertStatus(403);
    }

    public function test_manager_can_reject_pending_document(): void
    {
        $document = Document::create([
            'user_id' => $this->employeeA->id,
            'title' => 'Pending Document',
            'file_path' => 'docs/p.pdf',
            'state' => 'pending',
        ]);

        $response = $this->actingAs($this->manager)
            ->post(route('documents.reject', $document));

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'state' => 'rejected',
        ]);
    }
}

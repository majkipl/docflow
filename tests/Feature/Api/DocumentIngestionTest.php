<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use App\Services\Upload\FileUploaderInterface;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentIngestionTest extends TestCase
{
    use RefreshDatabase;

    private Role $employeeRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeRole = Role::create(['name' => 'employee']);
    }

    public function test_unauthenticated_user_cannot_upload_document_via_api(): void
    {
        $response = $this->postJson('/api/documents', [
            'title' => 'Invoice 2026',
            'file' => UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_without_permission_cannot_upload_document_via_api(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/documents', [
            'title' => 'Invoice 2026',
            'file' => UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_upload_document_via_api(): void
    {
        Storage::fake('local');

        /** @var User $user */
        $user = User::factory()->create();
        $user->roles()->attach($this->employeeRole);
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('agreement.pdf', 250, 'application/pdf');

        $response = $this->postJson('/api/documents', [
            'title' => 'Framework Agreement',
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'document_id',
                'status',
            ])
            ->assertJson([
                'message' => 'Dokument został pomyślnie zaimportowany.',
                'status' => 'draft',
            ]);

        $documentId = $response->json('document_id');

        $this->assertDatabaseHas('documents', [
            'id' => $documentId,
            'user_id' => $user->id,
            'title' => 'Framework Agreement',
            'state' => 'draft',
        ]);
    }

    public function test_upload_fails_validation_for_invalid_mime_type(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->roles()->attach($this->employeeRole);
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('malicious.exe', 100);

        $response = $this->postJson('/api/documents', [
            'title' => 'Malicious Executable',
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_validation_fails_when_required_fields_are_missing(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->roles()->attach($this->employeeRole);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/documents', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'file']);
    }

    public function test_upload_handles_uploader_exception_gracefully(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->roles()->attach($this->employeeRole);
        Sanctum::actingAs($user);

        $mockUploader = $this->mock(FileUploaderInterface::class);
        $mockUploader->shouldReceive('upload')
            ->andThrow(new Exception('Disk full'));

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/documents', [
            'title' => 'Valid Document',
            'file' => $file,
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Wystąpił błąd podczas wgrywania dokumentu.',
            ])
            ->assertJsonMissing([
                'error' => 'Disk full',
            ]);
    }

    public function test_authenticated_user_can_fetch_profile_via_api(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'API User',
            'email' => 'api_user@docflow.local',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => 'API User',
                'email' => 'api_user@docflow.local',
            ]);
    }
}

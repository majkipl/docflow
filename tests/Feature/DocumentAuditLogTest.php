<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentAuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $employeeRole = Role::create(['name' => 'employee']);
        $managerRole = Role::create(['name' => 'manager']);

        $this->employee = User::factory()->create(['name' => 'John Doe']);
        $this->employee->roles()->attach($employeeRole);

        $this->manager = User::factory()->create(['name' => 'Jane Manager']);
        $this->manager->roles()->attach($managerRole);
    }

    public function test_submitting_document_records_audit_log(): void
    {
        $document = Document::create([
            'user_id' => $this->employee->id,
            'title' => 'Audit Test Document',
            'file_path' => 'documents/test.pdf',
            'state' => 'draft',
        ]);

        $document->state->submit($this->employee);

        $this->assertDatabaseHas('document_audit_logs', [
            'document_id' => $document->id,
            'user_id' => $this->employee->id,
            'old_state' => 'draft',
            'new_state' => 'pending',
        ]);
    }

    public function test_full_document_lifecycle_creates_ordered_audit_trail(): void
    {
        $document = Document::create([
            'user_id' => $this->employee->id,
            'title' => 'Lifecycle Test Document',
            'file_path' => 'documents/lifecycle.pdf',
            'state' => 'draft',
        ]);

        $document->state->submit($this->employee);
        $document->state->approve($this->manager);

        $document->refresh();
        $this->assertCount(2, $document->auditLogs);

        $logs = $document->auditLogs()->orderBy('id', 'asc')->get();

        $this->assertEquals('draft', $logs[0]->old_state);
        $this->assertEquals('pending', $logs[0]->new_state);
        $this->assertEquals($this->employee->id, $logs[0]->user_id);

        $this->assertEquals('pending', $logs[1]->old_state);
        $this->assertEquals('approved', $logs[1]->new_state);
        $this->assertEquals($this->manager->id, $logs[1]->user_id);
    }

    public function test_rejection_lifecycle_creates_audit_trail(): void
    {
        $document = Document::create([
            'user_id' => $this->employee->id,
            'title' => 'Rejection Test Document',
            'file_path' => 'documents/reject.pdf',
            'state' => 'draft',
        ]);

        $document->state->submit($this->employee);
        $document->state->reject($this->manager);

        $this->assertDatabaseHas('document_audit_logs', [
            'document_id' => $document->id,
            'user_id' => $this->manager->id,
            'old_state' => 'pending',
            'new_state' => 'rejected',
        ]);
    }
}

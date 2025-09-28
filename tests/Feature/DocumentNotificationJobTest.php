<?php

namespace Tests\Feature;

use App\Jobs\SendDocumentStatusNotification;
use App\Mail\DocumentStatusChangedMail;
use App\Models\Document;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DocumentNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        $employeeRole = Role::create(['name' => 'employee']);

        $this->employee = User::factory()->create([
            'name' => 'Alice Employee',
            'email' => 'alice@docflow.local',
        ]);
        $this->employee->roles()->attach($employeeRole);

        $this->document = Document::create([
            'user_id' => $this->employee->id,
            'title' => 'Financial Report Q3',
            'file_path' => 'documents/report_q3.pdf',
            'state' => 'draft',
        ]);
    }

    public function test_state_change_dispatches_notification_job(): void
    {
        Queue::fake();

        $this->document->state->submit($this->employee);

        Queue::assertPushed(SendDocumentStatusNotification::class, function (SendDocumentStatusNotification $job) {
            return $job->document->id === $this->document->id
                && $job->oldState === 'draft'
                && $job->newState === 'pending';
        });
    }

    public function test_send_document_status_notification_job_sends_mailable(): void
    {
        Mail::fake();

        $job = new SendDocumentStatusNotification($this->document, 'draft', 'pending');
        $job->handle();

        Mail::assertSent(DocumentStatusChangedMail::class, function (DocumentStatusChangedMail $mail) {
            return $mail->hasTo('alice@docflow.local')
                && $mail->document->id === $this->document->id
                && $mail->oldState === 'draft'
                && $mail->newState === 'pending';
        });
    }

    public function test_document_status_changed_mail_renders_content_correctly(): void
    {
        $mailable = new DocumentStatusChangedMail($this->document, 'draft', 'pending');

        $mailable->assertHasSubject("Zmiana statusu dokumentu: Financial Report Q3");
        $mailable->assertSeeInHtml('Financial Report Q3');
        $mailable->assertSeeInHtml('draft');
        $mailable->assertSeeInHtml('pending');
        $mailable->assertSeeInHtml('/documents/' . $this->document->id);
    }

    public function test_notification_job_handles_document_without_user_gracefully(): void
    {
        Mail::fake();

        $orphanDocument = new Document([
            'title' => 'Orphan Document',
            'file_path' => 'documents/orphan.pdf',
            'state' => 'draft',
        ]);
        $orphanDocument->setRelation('user', null);

        $job = new SendDocumentStatusNotification($orphanDocument, 'draft', 'pending');
        $job->handle();

        Mail::assertNothingSent();
    }
}

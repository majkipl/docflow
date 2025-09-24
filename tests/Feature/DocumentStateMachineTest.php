<?php

namespace Tests\Feature;

use App\Domain\States\ApprovedState;
use App\Domain\States\PendingState;
use App\Domain\States\RejectedState;
use App\Events\DocumentStateChanged;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Tests\TestCase;

class DocumentStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private Document $document;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->document = Document::create([
            'user_id' => $this->user->id,
            'title' => 'Test Document',
            'file_path' => 'dummy/path.pdf',
            'state' => 'draft',
        ]);
    }

    public function test_draft_can_transition_to_pending_with_explicit_actor(): void
    {
        Event::fake();

        $this->document->state->submit($this->user);

        $this->document->refresh();
        $this->assertInstanceOf(PendingState::class, $this->document->state);
        $this->assertEquals('pending', $this->document->state->getName());

        Event::assertDispatched(DocumentStateChanged::class, function (DocumentStateChanged $event) {
            return $event->document->id === $this->document->id
                && $event->oldState === 'draft'
                && $event->newState === 'pending'
                && $event->userId === $this->user->id;
        });
    }

    public function test_draft_can_transition_to_pending_without_actor(): void
    {
        Event::fake();

        $this->document->state->submit();

        $this->document->refresh();
        $this->assertInstanceOf(PendingState::class, $this->document->state);

        Event::assertDispatched(DocumentStateChanged::class, function (DocumentStateChanged $event) {
            return $event->userId === null;
        });
    }

    public function test_draft_cannot_transition_directly_to_approved(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nie można zaakceptować dokumentu ze statusu: draft');

        $this->document->state->approve();
    }

    public function test_pending_can_transition_to_approved(): void
    {
        $this->document->state->submit($this->user);
        $this->document->state->approve($this->user);

        $this->document->refresh();
        $this->assertInstanceOf(ApprovedState::class, $this->document->state);
        $this->assertEquals('approved', $this->document->state->getName());
    }

    public function test_pending_can_transition_to_rejected(): void
    {
        $this->document->state->submit($this->user);
        $this->document->state->reject($this->user);

        $this->document->refresh();
        $this->assertInstanceOf(RejectedState::class, $this->document->state);
        $this->assertEquals('rejected', $this->document->state->getName());
    }
}

<?php

namespace Tests\Feature;

use App\Domain\States\ApprovedState;
use App\Domain\States\PendingState;
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

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->document = Document::create([
            'user_id' => $user->id,
            'title' => 'Test Document',
            'file_path' => 'dummy/path.pdf',
            'state' => 'draft',
        ]);
    }

    public function test_draft_can_transition_to_pending()
    {
        Event::fake(); // Blokujemy rzeczywiste rzucanie eventów, by je asertować

        $this->document->state->submit();

        $this->document->refresh();
        $this->assertInstanceOf(PendingState::class, $this->document->state);
        $this->assertEquals('pending', $this->document->state->getName());

        Event::assertDispatched(DocumentStateChanged::class);
    }

    public function test_draft_cannot_transition_directly_to_approved()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nie można zaakceptować dokumentu ze statusu: draft');

        $this->document->state->approve();
    }

    public function test_pending_can_transition_to_approved()
    {
        // Najpierw przechodzimy do pending
        $this->document->state->submit();

        // Następnie akceptujemy
        $this->document->state->approve();

        $this->document->refresh();
        $this->assertInstanceOf(ApprovedState::class, $this->document->state);
    }
}

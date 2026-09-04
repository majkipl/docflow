<?php

namespace Tests\Unit\Domain\States;

use App\Domain\States\ApprovedState;
use App\Domain\States\DraftState;
use App\Domain\States\PendingState;
use App\Domain\States\RejectedState;
use App\Models\Document;
use App\Models\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DocumentStateTest extends TestCase
{
    private Document $document;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->document = new Document();
        $this->user = new User();
    }

    public function test_get_name_returns_proper_status_for_each_state(): void
    {
        $this->assertSame('draft', (new DraftState($this->document))->getName());
        $this->assertSame('pending', (new PendingState($this->document))->getName());
        $this->assertSame('approved', (new ApprovedState($this->document))->getName());
        $this->assertSame('rejected', (new RejectedState($this->document))->getName());
    }

    public function test_draft_state_disallows_approve_and_reject_transitions(): void
    {
        $draftState = new DraftState($this->document);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nie można zaakceptować dokumentu ze statusu: draft');
        $draftState->approve($this->user);
    }

    public function test_draft_state_disallows_reject_transition(): void
    {
        $draftState = new DraftState($this->document);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nie można odrzucić dokumentu ze statusu: draft');
        $draftState->reject($this->user);
    }

    public function test_pending_state_disallows_submit_transition(): void
    {
        $pendingState = new PendingState($this->document);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nie można przesłać dokumentu ze statusu: pending');
        $pendingState->submit($this->user);
    }

    public function test_approved_state_disallows_all_further_transitions(): void
    {
        $approvedState = new ApprovedState($this->document);

        try {
            $approvedState->submit($this->user);
            $this->fail('Expected submit to throw InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Nie można przesłać dokumentu ze statusu: approved', $e->getMessage());
        }

        try {
            $approvedState->approve($this->user);
            $this->fail('Expected approve to throw InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Nie można zaakceptować dokumentu ze statusu: approved', $e->getMessage());
        }

        try {
            $approvedState->reject($this->user);
            $this->fail('Expected reject to throw InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Nie można odrzucić dokumentu ze statusu: approved', $e->getMessage());
        }
    }

    public function test_rejected_state_disallows_all_further_transitions(): void
    {
        $rejectedState = new RejectedState($this->document);

        try {
            $rejectedState->submit($this->user);
            $this->fail('Expected submit to throw InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Nie można przesłać dokumentu ze statusu: rejected', $e->getMessage());
        }

        try {
            $rejectedState->approve($this->user);
            $this->fail('Expected approve to throw InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Nie można zaakceptować dokumentu ze statusu: rejected', $e->getMessage());
        }

        try {
            $rejectedState->reject($this->user);
            $this->fail('Expected reject to throw InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Nie można odrzucić dokumentu ze statusu: rejected', $e->getMessage());
        }
    }
}

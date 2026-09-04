<?php

namespace Tests\Unit\Casts;

use App\Casts\StateCast;
use App\Domain\Enums\DocumentStatus;
use App\Domain\States\ApprovedState;
use App\Domain\States\DraftState;
use App\Domain\States\PendingState;
use App\Domain\States\RejectedState;
use App\Models\Document;
use App\Models\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StateCastTest extends TestCase
{
    private StateCast $cast;
    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new StateCast();
        $this->document = new Document();
    }

    public function test_get_returns_correct_state_instance_for_each_status(): void
    {
        $draftState = $this->cast->get($this->document, 'state', 'draft', []);
        $this->assertInstanceOf(DraftState::class, $draftState);
        $this->assertSame('draft', $draftState->getName());

        $pendingState = $this->cast->get($this->document, 'state', 'pending', []);
        $this->assertInstanceOf(PendingState::class, $pendingState);
        $this->assertSame('pending', $pendingState->getName());

        $approvedState = $this->cast->get($this->document, 'state', 'approved', []);
        $this->assertInstanceOf(ApprovedState::class, $approvedState);
        $this->assertSame('approved', $approvedState->getName());

        $rejectedState = $this->cast->get($this->document, 'state', 'rejected', []);
        $this->assertInstanceOf(RejectedState::class, $rejectedState);
        $this->assertSame('rejected', $rejectedState->getName());
    }

    public function test_get_throws_exception_when_model_is_not_document(): void
    {
        $nonDocumentModel = new User();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('StateCast can only be used with the Document model.');

        $this->cast->get($nonDocumentModel, 'state', 'draft', []);
    }

    public function test_get_throws_exception_for_unknown_status_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nieznany status: archived');

        $this->cast->get($this->document, 'state', 'archived', []);
    }

    public function test_get_throws_exception_for_non_string_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cast->get($this->document, 'state', 123, []);
    }

    public function test_set_returns_status_string_for_document_state_instance(): void
    {
        $draftState = new DraftState($this->document);
        $this->assertSame('draft', $this->cast->set($this->document, 'state', $draftState, []));

        $pendingState = new PendingState($this->document);
        $this->assertSame('pending', $this->cast->set($this->document, 'state', $pendingState, []));

        $approvedState = new ApprovedState($this->document);
        $this->assertSame('approved', $this->cast->set($this->document, 'state', $approvedState, []));

        $rejectedState = new RejectedState($this->document);
        $this->assertSame('rejected', $this->cast->set($this->document, 'state', $rejectedState, []));
    }

    public function test_set_returns_status_string_for_document_status_enum(): void
    {
        $this->assertSame('draft', $this->cast->set($this->document, 'state', DocumentStatus::DRAFT, []));
        $this->assertSame('pending', $this->cast->set($this->document, 'state', DocumentStatus::PENDING, []));
        $this->assertSame('approved', $this->cast->set($this->document, 'state', DocumentStatus::APPROVED, []));
        $this->assertSame('rejected', $this->cast->set($this->document, 'state', DocumentStatus::REJECTED, []));
    }

    public function test_set_returns_string_when_valid_status_string_is_provided(): void
    {
        $this->assertSame('draft', $this->cast->set($this->document, 'state', 'draft', []));
        $this->assertSame('pending', $this->cast->set($this->document, 'state', 'pending', []));
        $this->assertSame('approved', $this->cast->set($this->document, 'state', 'approved', []));
        $this->assertSame('rejected', $this->cast->set($this->document, 'state', 'rejected', []));
    }

    public function test_set_throws_exception_when_invalid_status_string_is_provided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nieznany status: invalid_status');

        $this->cast->set($this->document, 'state', 'invalid_status', []);
    }

    public function test_set_throws_exception_when_invalid_type_is_provided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('State value must be a string, DocumentStatus or instance of DocumentState.');

        $this->cast->set($this->document, 'state', 42, []);
    }
}

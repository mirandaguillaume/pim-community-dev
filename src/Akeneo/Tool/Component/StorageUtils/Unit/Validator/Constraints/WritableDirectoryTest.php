<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Validator\Constraints;

use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectory;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectoryValidator;
use PHPUnit\Framework\TestCase;

class WritableDirectoryTest extends TestCase
{
    private WritableDirectory $sut;

    protected function setUp(): void
    {
        $this->sut = new WritableDirectory();
    }

    public function test_it_is_a_constraint(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Validator\Constraint::class, $this->sut);
    }

    public function test_it_has_a_message(): void
    {
        $this->assertSame('This directory is not writable', $this->sut->message);
    }

    public function test_it_has_a_message_for_invalid_directory(): void
    {
        $this->assertSame('This directory is not valid', $this->sut->invalidMessage);
    }

    public function test_it_is_validated_by_writable_validator(): void
    {
        $this->assertSame(WritableDirectoryValidator::class, $this->sut->validatedBy());
    }
}

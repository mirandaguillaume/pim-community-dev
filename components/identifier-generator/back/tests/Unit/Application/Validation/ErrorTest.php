<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    private Error $sut;

    protected function setUp(): void
    {
        $this->sut = new Error(
            'a message',
            ['parameter1' => 'value1'],
            'a path',
        );
    }

    public function test_it_should_be_normalized(): void
    {
        $this->assertSame([
            'path' => 'a path',
            'message' => 'a message',
        ], $this->sut->normalize());
    }
}

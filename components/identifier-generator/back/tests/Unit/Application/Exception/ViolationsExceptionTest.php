<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;
use PHPUnit\Framework\TestCase;

class ViolationsExceptionTest extends TestCase
{
    private ViolationsException $sut;

    protected function setUp(): void
    {
        $this->sut = new ViolationsException(new ErrorList([
            new Error('message1'),
            new Error('message2'),
        ]));
    }

    public function test_it_should_be_normalized(): void
    {
        $this->assertSame([
            [
                'path' => null,
                'message' => 'message1',
            ],
            [
                'path' => null,
                'message' => 'message2',
            ],
        ], $this->sut->normalize());
    }
}

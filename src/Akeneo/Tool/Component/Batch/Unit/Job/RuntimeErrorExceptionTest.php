<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\RuntimeErrorException;
use PHPUnit\Framework\TestCase;

class RuntimeErrorExceptionTest extends TestCase
{
    private RuntimeErrorException $sut;

    protected function setUp(): void
    {
        $this->sut = new RuntimeErrorException('my message %myparam%', ['%myparam%' => 'param']);
    }

    public function test_it_provides_message_parameters(): void
    {
        $this->assertSame(['%myparam%' => 'param'], $this->sut->getMessageParameters());
    }
}

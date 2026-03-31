<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\FileStorage;

use Akeneo\Test\Acceptance\FileStorage\InMemoryFileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PHPUnit\Framework\TestCase;

class InMemoryFileStorerTest extends TestCase
{
    private InMemoryFileStorer $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryFileStorer();
    }

}

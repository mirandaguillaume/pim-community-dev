<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Event;

use Akeneo\Pim\Structure\Component\Event\FamilyWasCreated;
use PHPUnit\Framework\TestCase;

class FamilyWasCreatedTest extends TestCase
{
    private FamilyWasCreated $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyWasCreated();
    }

}

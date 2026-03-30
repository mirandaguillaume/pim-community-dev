<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Event;

use Akeneo\Pim\Structure\Component\Event\FamilyWasUpdated;
use PHPUnit\Framework\TestCase;

class FamilyWasUpdatedTest extends TestCase
{
    private FamilyWasUpdated $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyWasUpdated();
    }

}

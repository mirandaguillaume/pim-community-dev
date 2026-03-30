<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Event;

use Akeneo\Pim\Structure\Component\Event\AttributeWasCreated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use PHPUnit\Framework\TestCase;

class AttributesWereCreatedOrUpdatedTest extends TestCase
{
    private AttributesWereCreatedOrUpdated $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributesWereCreatedOrUpdated();
    }

}

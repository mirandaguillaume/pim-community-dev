<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Group;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    private Group $sut;

    protected function setUp(): void
    {
        $this->sut = new Group();
    }

}

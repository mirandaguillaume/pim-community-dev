<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Test\Acceptance\Attribute\InMemoryGetExistingReferenceDataCodes;
use PHPUnit\Framework\TestCase;

class InMemoryGetExistingReferenceDataCodesTest extends TestCase
{
    private InMemoryGetExistingReferenceDataCodes $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGetExistingReferenceDataCodes();
    }

}

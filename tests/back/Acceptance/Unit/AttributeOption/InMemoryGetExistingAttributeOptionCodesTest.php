<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryGetExistingAttributeOptionCodes;
use PHPUnit\Framework\TestCase;

class InMemoryGetExistingAttributeOptionCodesTest extends TestCase
{
    private InMemoryGetExistingAttributeOptionCodes $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGetExistingAttributeOptionCodes();
    }

}

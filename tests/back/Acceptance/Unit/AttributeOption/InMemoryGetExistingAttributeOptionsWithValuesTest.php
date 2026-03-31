<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryGetExistingAttributeOptionsWithValues;
use PHPUnit\Framework\TestCase;

class InMemoryGetExistingAttributeOptionsWithValuesTest extends TestCase
{
    private InMemoryGetExistingAttributeOptionsWithValues $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGetExistingAttributeOptionsWithValues();
    }

}

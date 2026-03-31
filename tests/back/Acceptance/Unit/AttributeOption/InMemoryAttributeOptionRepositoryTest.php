<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;

class InMemoryAttributeOptionRepositoryTest extends TestCase
{
    private InMemoryAttributeOptionRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryAttributeOptionRepository();
    }

    private function createAttributeOption(string $code, AttributeInterface $attribute): AttributeOptionInterface
    {
        $attributeOption = new AttributeOption();
        $attributeOption->setCode($code);
        $attributeOption->setAttribute($attribute);
    
        return $attributeOption;
    }
}

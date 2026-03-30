<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\AttributeGroup;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;

class InMemoryAttributeGroupRepositoryTest extends TestCase
{
    private InMemoryAttributeGroupRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryAttributeGroupRepository();
    }

    private function createAttributeGroup(string $code): AttributeGroupInterface
    {
        $attributeGroup = new AttributeGroup();
        $attributeGroup->setCode($code);
    
        return $attributeGroup;
    }
}

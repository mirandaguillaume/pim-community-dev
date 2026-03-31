<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;

class InMemoryAttributeRepositoryTest extends TestCase
{
    private InMemoryAttributeRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryAttributeRepository();
    }

    private function createAttribute(string $code, string $type = null, string $backendType = null): AttributeInterface
    {
        $attribute = new Attribute();
        $attribute->setCode($code);
        if (null !== $type) {
            $attribute->setType($type);
        }
    
        if (null !== $backendType) {
            $attribute->setBackendType($backendType);
        }
    
        return $attribute;
    }
}

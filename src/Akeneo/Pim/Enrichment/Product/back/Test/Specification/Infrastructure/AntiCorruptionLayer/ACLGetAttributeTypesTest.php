<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer\ACLGetAttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ACLGetAttributeTypesTest extends TestCase
{
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private ACLGetAttributeTypes $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->sut = new ACLGetAttributeTypes($this->attributeRepository);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ACLGetAttributeTypes::class, $this->sut);
    }

    public function test_it_returns_attribute_types_from_attribute_codes(): void
    {
        $this->attributeRepository->method('getAttributeTypeByCodes')->with(['sku', 'name', 'unknown'])->willReturn([
                    'sku' => 'pim_catalog_identifier',
                    'name' => 'pim_catalog_text',
                ]);
        $this->assertSame([
                    'sku' => 'pim_catalog_identifier',
                    'name' => 'pim_catalog_text',
                ], $this->sut->fromAttributeCodes(['sku', 'name', 'unknown']));
        $this->assertSame([], $this->sut->fromAttributeCodes([]));
    }

    public function test_it_throws_an_exception_when_input_data_is_not_valid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->fromAttributeCodes(['sku', true]);
    }
}

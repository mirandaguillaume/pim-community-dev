<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGenerator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MaskItemGeneratorTest extends TestCase
{
    private MaskItemGeneratorForAttributeType|MockObject $generator1;
    private MaskItemGeneratorForAttributeType|MockObject $generator2;
    private MaskItemGeneratorForAttributeType|MockObject $generator3;
    private MaskItemGenerator $sut;

    protected function setUp(): void
    {
        $this->generator1 = $this->createMock(MaskItemGeneratorForAttributeType::class);
        $this->generator2 = $this->createMock(MaskItemGeneratorForAttributeType::class);
        $this->generator3 = $this->createMock(MaskItemGeneratorForAttributeType::class);
        $this->sut = new MaskItemGenerator([$this->generator1, $this->generator2, $this->generator3]);
        $this->generator1->method('supportedAttributeTypes')->willReturn([]);
        $this->generator2->method('supportedAttributeTypes')->willReturn(['attributeType2']);
        $this->generator3->method('supportedAttributeTypes')->willReturn(['attributeType3', 'attributeType3bis']);
    }

    public function test_it_is_a_mask_item_generator(): void
    {
        $this->assertInstanceOf(MaskItemGenerator::class, $this->sut);
    }

    public function test_it_returns_existing_generator(): void
    {
        $this->generator1->expects($this->never())->method('forRawValue');
        $this->generator2->expects($this->once())->method('forRawValue')->with('attributeCode2', 'channelCode', 'localeCode', 'value')->willReturn(['mask']);
        $this->generator3->expects($this->never())->method('forRawValue');
        $this->assertSame(['mask'], $this->sut->generate('attributeCode2', 'attributeType2', 'channelCode', 'localeCode', 'value'));
    }

    public function test_it_should_throw_exception_on_non_existing_generator(): void
    {
        $this->expectException(new \LogicException('MaskItemGenerator for attribute type "nonExistingAttributeType" not found'));
        $this->sut->generate('attributeCode', 'nonExistingAttributeType', 'channelCode', 'localeCode', 'value');
    }
}

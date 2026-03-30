<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MetricMaskItemGenerator;
use PHPUnit\Framework\TestCase;

class MetricMaskItemGeneratorTest extends TestCase
{
    private MetricMaskItemGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricMaskItemGenerator();
        $this->sut->beConstructedWith();
    }

    public function test_it_is_a_mask_item_generator(): void
    {
        $this->assertInstanceOf(MaskItemGeneratorForAttributeType::class, $this->sut);
    }

    public function test_it_adds_mask_on_filled_metric(): void
    {
        $value = ['amount' => 200, 'unit' => 'UNIT', 'base_data' => 0.2, 'base_unit' => 'BASEUNIT'];
        $this->assertSame(['attributeCode-channelCode-localeCode'], $this->sut->forRawValue('attributeCode', 'channelCode', 'localeCode', $value));
    }

    public function test_it_does_not_add_mask_when_missing_unit(): void
    {
        $value = ['amount' => 200, 'base_data' => 0.2, 'base_unit' => 'BASEUNIT'];
        $this->assertSame([], $this->sut->forRawValue('attributeCode', 'channelCode', 'localeCode', $value));
    }

    public function test_it_does_not_add_mask_when_missing_amount(): void
    {
        $value = ['unit' => 'UNIT', 'base_data' => 0.2, 'base_unit' => 'BASEUNIT'];
        $this->assertSame([], $this->sut->forRawValue('attributeCode', 'channelCode', 'localeCode', $value));
    }

    public function test_it_does_not_add_mask_when_missing_base_data(): void
    {
        $value = ['amount' => 200, 'unit' => 'UNIT', 'base_unit' => 'BASEUNIT'];
        $this->assertSame([], $this->sut->forRawValue('attributeCode', 'channelCode', 'localeCode', $value));
    }

    public function test_it_does_not_add_mask_when_missing_base_unit(): void
    {
        $value = ['amount' => 200, 'unit' => 'UNIT', 'base_data' => 0.2];
        $this->assertSame([], $this->sut->forRawValue('attributeCode', 'channelCode', 'localeCode', $value));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\DefaultMaskItemGenerator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use PHPUnit\Framework\TestCase;

class DefaultMaskItemGeneratorTest extends TestCase
{
    private DefaultMaskItemGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new DefaultMaskItemGenerator();
    }

    public function test_it_is_a_mask_item_generator(): void
    {
        $this->assertInstanceOf(MaskItemGeneratorForAttributeType::class, $this->sut);
    }

    public function test_it_returns_default_mask(): void
    {
        $this->assertSame(['attributeCode-channelCode-localeCode'], $this->sut->forRawValue('attributeCode', 'channelCode', 'localeCode', 'value'));
    }
}

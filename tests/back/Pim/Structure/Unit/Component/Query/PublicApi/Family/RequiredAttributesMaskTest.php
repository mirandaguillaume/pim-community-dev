<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Query\PublicApi\Family;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMaskForChannelAndLocale;
use PHPUnit\Framework\TestCase;

class RequiredAttributesMaskTest extends TestCase
{
    private RequiredAttributesMask $sut;

    protected function setUp(): void
    {
        $this->sut = new RequiredAttributesMask('family_code', [
                new RequiredAttributesMaskForChannelAndLocale(
                    'ecommerce',
                    'en_US',
                    ['name-ecommerce-en_US', 'view-ecommerce-en_US']
                ),
                new RequiredAttributesMaskForChannelAndLocale(
                    '<all_channels>',
                    '<all_locales>',
                    ['desc-<all_channels>-<all_locales>']
                )
            ]);
    }

    public function test_it_returns_attribute_requirement_mask_for_a_channel_and_a_locale(): void
    {
        $this->assertEquals(new RequiredAttributesMaskForChannelAndLocale(
                        'ecommerce',
                        'en_US',
                        ['name-ecommerce-en_US', 'view-ecommerce-en_US']
                    ), $this->sut->requiredAttributesMaskForChannelAndLocale('ecommerce', 'en_US'));
    }

    public function test_it_throws_exception_if_attribute_requirement_mask_not_found(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->requiredAttributesMaskForChannelAndLocale('test', 'en_US');
    }

    public function test_it_merges_with_another_mask(): void
    {
        $other = new RequiredAttributesMask('family_code', [
                    new RequiredAttributesMaskForChannelAndLocale(
                        'ecommerce',
                        'fr_FR',
                        ['image-ecommerce-fr_FR']
                    ),
                    new RequiredAttributesMaskForChannelAndLocale(
                        '<all_channels>',
                        '<all_locales>',
                        ['desc-<all_channels>-<all_locales>', 'color-<all_channels>-<all_locales>']
                    ),
                ]);
        $this->assertEquals(new RequiredAttributesMask('family_code', [
                    new RequiredAttributesMaskForChannelAndLocale(
                        'ecommerce',
                        'en_US',
                        ['name-ecommerce-en_US', 'view-ecommerce-en_US']
                    ),
                    new RequiredAttributesMaskForChannelAndLocale(
                        '<all_channels>',
                        '<all_locales>',
                        ['desc-<all_channels>-<all_locales>', 'color-<all_channels>-<all_locales>']
                    ),
                    new RequiredAttributesMaskForChannelAndLocale(
                        'ecommerce',
                        'fr_FR',
                        ['image-ecommerce-fr_FR']
                    ),
                ]), $this->sut->merge($other));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Channels\InMemoryChannels;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales\InMemoryLocales;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformChannelLocaleDataIds;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformChannelLocaleDataIdsTest extends TestCase
{
    private TransformChannelLocaleDataIds $sut;

    protected function setUp(): void
    {
        $this->sut = new TransformChannelLocaleDataIds($channels, $locales);
        $channels = new InMemoryChannels([
        'ecommerce' => 1,
        'mobile' => 2,
        ]);
        $locales = new InMemoryLocales([
        'en_US' => 58,
        'fr_FR' => 90,
        ]);
    }

    public function test_it_transforms_channels_and_locales_with_data_from_ids_to_codes(): void
    {
        $dataToTransform = [
                    1 => [
                        58 => [12, 34],
                        90 => [34],
                    ],
                    2 => [
                        58 => [12, 34, 56],
                    ],
                ];
        $expectedTransformedData = [
                    'ecommerce' => [
                        'en_US' => 2,
                        'fr_FR' => 1,
                    ],
                    'mobile' => [
                        'en_US' => 3,
                    ],
                ];
        $this->assertSame($expectedTransformedData, $this->sut->transformToCodes($dataToTransform, fn ($elements) => is_countable($elements) ? count($elements) : 0));
    }

    public function test_it_removes_unknown_channels_and_locales_during_transformation(): void
    {
        $dataToTransform = [
                    1 => [
                        58 => [12, 34],
                        789 => [34],
                        90 => [34],
                    ],
                    76 => [
                        58 => [12, 34, 56],
                    ],
                ];
        $expectedTransformedData = [
                    'ecommerce' => [
                        'en_US' => 2,
                        'fr_FR' => 1,
                    ],
                ];
        $this->assertSame($expectedTransformedData, $this->sut->transformToCodes($dataToTransform, fn ($elements) => is_countable($elements) ? count($elements) : 0));
    }
}

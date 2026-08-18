<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\OnlyEnterpriseEditionFeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OnlyEnterpriseEditionFeatureFlagTest extends TestCase
{
    public function test_it_is_a_feature_flag(): void
    {
        $this->assertInstanceOf(FeatureFlag::class, new OnlyEnterpriseEditionFeatureFlag('flexibility_instance'));
    }

    /** @dataProvider enterpriseEditions */
    public function test_it_is_enabled_on_enterprise_editions(string $edition): void
    {
        $sut = new OnlyEnterpriseEditionFeatureFlag($edition);

        $this->assertTrue($sut->isEnabled());
    }

    public static function enterpriseEditions(): iterable
    {
        yield 'flexibility' => ['flexibility_instance'];
        yield 'serenity' => ['serenity_instance'];
    }

    public function test_it_is_disabled_on_the_community_edition(): void
    {
        $sut = new OnlyEnterpriseEditionFeatureFlag('community_instance');

        $this->assertFalse($sut->isEnabled());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\FeatureFlag;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class DataQualityInsightsFeature implements FeatureFlag
{
    public function __construct(private bool $activationFlag) {}

    public function isEnabled(?string $feature = null): bool
    {
        return (true === $this->activationFlag);
    }
}

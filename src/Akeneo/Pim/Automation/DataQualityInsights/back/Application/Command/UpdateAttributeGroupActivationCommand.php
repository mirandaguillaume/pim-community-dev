<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Command;

final readonly class UpdateAttributeGroupActivationCommand
{
    public function __construct(
        public string $attributeGroupCode,
        public bool $isActivated
    ) {
    }
}

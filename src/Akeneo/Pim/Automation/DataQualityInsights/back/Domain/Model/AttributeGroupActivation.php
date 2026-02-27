<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class AttributeGroupActivation
{
    public function __construct(private AttributeGroupCode $attributeGroupCode, private bool $activated) {}

    public function getAttributeGroupCode(): AttributeGroupCode
    {
        return $this->attributeGroupCode;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }
}

<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Url;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute): bool
    {
        return AttributeTypes::TEXT === $attribute->getType();
    }

    /**
     * {@inheritdoc}
     * @return list<\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Url>
     */
    public function guessConstraints(AttributeInterface $attribute): array
    {
        $constraints = [];
        if ('url' === $attribute->getValidationRule()) {
            $constraints[] = new Url(['attributeCode' => $attribute->getCode()]);
        }

        return $constraints;
    }
}

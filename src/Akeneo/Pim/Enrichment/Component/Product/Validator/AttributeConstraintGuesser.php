<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Attribute constraint guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeConstraintGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @return list<(Symfony\Component\Validator\Constraints\Date | Symfony\Component\Validator\Constraints\DateTime | Symfony\Component\Validator\Constraints\NotBlank)>
     */
    public function guessConstraints(AttributeInterface $attribute): array
    {
        $constraints = [];

        if ($attribute->isRequired()) {
            $constraints[] = new Constraints\NotBlank();
        }

        switch ($attribute->getBackendType()) {
            case AttributeTypes::BACKEND_TYPE_DATE:
                $constraints[] = new Constraints\Date();
                break;
            case AttributeTypes::BACKEND_TYPE_DATETIME:
                $constraints[] = new Constraints\DateTime();
                break;
        }

        return $constraints;
    }
}

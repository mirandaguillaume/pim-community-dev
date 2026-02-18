<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * EmptyValue provider for attributes
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseEmptyValueProvider implements EmptyValueProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmptyValue($attribute)
    {
        $emptyValue = match ($attribute->getType()) {
            AttributeTypes::METRIC => [
                'amount' => null,
                'unit'   => $attribute->getDefaultMetricUnit(),
            ],
            AttributeTypes::OPTION_MULTI_SELECT, AttributeTypes::PRICE_COLLECTION => [],
            AttributeTypes::TEXT => '',
            AttributeTypes::BOOLEAN => false,
            default => null,
        };

        return $emptyValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface;
    }
}

<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OnlyExpectedAttributes extends Constraint
{
    final public const string ATTRIBUTE_UNEXPECTED = 'pim_catalog.constraint.can_have_family_variant_unexpected_attribute';
    final public const string ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY = 'pim_catalog.constraint.attribute_does_not_belong_to_family';

    /** @var string */
    public $propertyPath = 'attribute';

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function validatedBy(): string
    {
        return 'pim_only_expected_attributes';
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getTargets(): string|array
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}

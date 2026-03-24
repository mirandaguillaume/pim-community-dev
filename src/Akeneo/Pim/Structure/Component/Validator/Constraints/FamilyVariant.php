<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariant extends Constraint
{
    final public const string FAMILY_VARIANT_NO_LEVEL = 'pim_catalog.constraint.family_variant_no_level';

    final public const string HAS_FAMILY_ATTRIBUTE = 'pim_catalog.constraint.family_variant_has_family_attribute';

    final public const string UNIQUE_ATTRIBUTE_IN_LAST_LEVEL
        = 'pim_catalog.constraint.family_variant_unique_attributes_in_last_level';

    final public const string ATTRIBUTES_UNIQUE = 'pim_catalog.constraint.family_variant_attributes_unique';

    final public const string AXES_WRONG_TYPE = 'pim_catalog.constraint.family_variant_axes_wrong_type';

    final public const string AXES_ATTRIBUTE_TYPE_UNIQUE = 'pim_catalog.constraint.family_variant_axes_attribute_type_unique';

    final public const string AXES_ATTRIBUTE_TYPE = 'pim_catalog.constraint.family_variant_axes_attribute_type';

    final public const string AXES_LEVEL = 'pim_catalog.constraint.family_variant_axis_level';

    final public const string AXES_UNIQUE = 'pim_catalog.constraint.family_variant_axes_unique';

    final public const string MAXIMUM_NUMBER_OF_LEVEL = 'pim_catalog.constraint.family_variant_maximum_number_of_level';

    final public const string LEVEL_DO_NOT_EXIST = 'pim_catalog.constraint.family_variant_level_do_not_exist';

    final public const string NUMBER_OF_AXES = 'pim_catalog.constraint.family_variant_axes_number_of_axes';

    final public const string NO_AXIS = 'pim_catalog.constraint.family_variant_no_axis';

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function validatedBy(): string
    {
        return 'pim_family_variant';
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

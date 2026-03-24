<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributePropertyType extends Constraint
{
    public string $message = '';
    public array $properties;
    public string $type;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getRequiredOptions(): array
    {
        return ['properties', 'type'];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function validatedBy(): string
    {
        return 'pim_structure_attribute_property_type_validator';
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\CreateMeasurementFamily;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Count extends Constraint
{
    final public const string MAX_MESSAGE = 'pim_measurements.validation.measurement_family.should_contain_max_elements';

    #[\Override]
    public function validatedBy(): string
    {
        return 'akeneo_measurement.validation.create_measurement_family.count';
    }

    #[\Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}

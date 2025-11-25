<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures only one identifier attribute exists in the catalog.
 */
class SingleIdentifierAttribute extends Constraint
{
    public string $message = 'An identifier attribute already exists.';

    public function validatedBy(): string
    {
        return SingleIdentifierAttributeValidator::class;
    }
}

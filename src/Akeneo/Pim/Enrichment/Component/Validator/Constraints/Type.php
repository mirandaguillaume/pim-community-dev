<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Type extends Constraint
{
    public string $message = '';
    public array $properties;
    public mixed $type;

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getRequiredOptions(): array
    {
        return ['type', 'message'];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function validatedBy(): string
    {
        return 'pim_enrichment_type_validator';
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that only one identifier attribute exists.
 */
class SingleIdentifierAttributeValidator extends ConstraintValidator
{
    public function __construct(private AttributeRepositoryInterface $attributeRepository)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SingleIdentifierAttribute) {
            throw new UnexpectedTypeException($constraint, SingleIdentifierAttribute::class);
        }

        if (!$value instanceof AttributeInterface) {
            return;
        }

        if ($value->getType() !== AttributeTypes::IDENTIFIER) {
            return;
        }

        $existingIdentifier = $this->attributeRepository->getIdentifier();
        $hasExistingIdentifier = !empty($this->attributeRepository->getAttributeCodesByType(AttributeTypes::IDENTIFIER));

        if (!$hasExistingIdentifier || null === $existingIdentifier) {
            return;
        }

        if (null !== $value->getId() && $existingIdentifier->getId() === $value->getId()) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}

<?php

namespace Akeneo\Platform\Bundle\UIBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Normalizes a ConstraintViolationList into an array of violation data.
 *
 * This normalizer directly extracts violation fields rather than delegating to the ObjectNormalizer,
 * because the recursive normalization of nested Constraint objects through the ObjectNormalizer is
 * fragile across PHP versions (especially PHP 8.2+) due to lazy property initialization in Constraint.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintViolationListNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function normalize($constraintList, $format = null, array $context = []): array|bool|string|int|float|\ArrayObject|null
    {
        $result = [];

        foreach ($constraintList as $violation) {
            $result[] = $this->normalizeViolation($violation);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ConstraintViolationListInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function normalizeViolation(ConstraintViolationInterface $violation): array
    {
        return [
            'messageTemplate' => $violation->getMessageTemplate(),
            'parameters' => $violation->getParameters(),
            'plural' => $violation->getPlural(),
            'message' => $violation->getMessage(),
            'root' => $this->normalizeRoot($violation->getRoot()),
            'propertyPath' => $violation->getPropertyPath(),
            'invalidValue' => $violation->getInvalidValue(),
            'constraint' => $this->normalizeConstraint($violation->getConstraint()),
            'cause' => $violation->getCause(),
            'code' => $violation->getCode(),
        ];
    }

    private function normalizeRoot(mixed $root): mixed
    {
        if (\is_object($root)) {
            return get_object_vars($root);
        }

        return $root;
    }

    private function normalizeConstraint(mixed $constraint): ?array
    {
        if (null === $constraint) {
            return null;
        }

        return [
            'targets' => $constraint->getTargets(),
            'defaultOption' => $constraint->getDefaultOption(),
            'requiredOptions' => $constraint->getRequiredOptions(),
            'message' => property_exists($constraint, 'message') ? $constraint->message : null,
            'payload' => $constraint->payload,
            'groups' => $constraint->groups,
        ];
    }
}

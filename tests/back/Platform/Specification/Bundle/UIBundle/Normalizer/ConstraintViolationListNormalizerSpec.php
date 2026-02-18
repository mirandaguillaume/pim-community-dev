<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use stdClass;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationListNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_violations()
    {
        $constraint = new NotBlank();
        $constraintA = new ConstraintViolation('constraint A', 'template A', [], null, 'path', null, null, null, $constraint);
        $constraintB = new ConstraintViolation('constraint B', 'template B', [], null, 'path', null, null, null, $constraint);
        $constraints = new ConstraintViolationList([$constraintA, $constraintB]);

        $result = $this->normalize($constraints);
        $result->shouldBeArray();
        $result->shouldHaveCount(2);
        $result[0]->shouldHaveKeyWithValue('message', 'constraint A');
        $result[1]->shouldHaveKeyWithValue('message', 'constraint B');
    }

    function it_supports_only_constraint_list()
    {
        $this->supportsNormalization(new stdClass())->shouldReturn(false);
        $this->supportsNormalization(new ConstraintViolationList([]))->shouldReturn(true);
    }
}

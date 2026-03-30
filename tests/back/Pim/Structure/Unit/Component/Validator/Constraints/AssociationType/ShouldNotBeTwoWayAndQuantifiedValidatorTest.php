<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints\AssociationType;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AssociationType\ShouldNotBeTwoWayAndQuantified;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AssociationType\ShouldNotBeTwoWayAndQuantifiedValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ShouldNotBeTwoWayAndQuantifiedValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private ShouldNotBeTwoWayAndQuantifiedValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new ShouldNotBeTwoWayAndQuantifiedValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_validate_that_association_type_is_not_two_way_and_quantified(): void
    {
        $constraint = $this->createMock(ShouldNotBeTwoWayAndQuantified::class);
        $associationType = $this->createMock(AssociationType::class);

        $associationType->method('isTwoWay')->willReturn(false);
        $associationType->method('isQuantified')->willReturn(true);
        $this->context->expects($this->never())->method('addViolation')->with($this->anything());
        $this->sut->validate($associationType, $constraint);
    }

    public function test_it_builds_violation_when_association_type_when_is_two_way_and_quantified(): void
    {
        $associationType = $this->createMock(AssociationType::class);
        $constraint = $this->createMock(ShouldNotBeTwoWayAndQuantified::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $associationType->method('isTwoWay')->willReturn(true);
        $associationType->method('isQuantified')->willReturn(true);
        $this->context->expects($this->once())->method('buildViolation')->with('pim_structure.validation.association_type.cannot_be_quantified_and_two_way')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($associationType, $constraint);
    }
}

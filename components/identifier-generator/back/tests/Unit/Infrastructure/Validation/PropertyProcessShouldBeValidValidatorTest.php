<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\PropertyProcessShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\PropertyProcessShouldBeValidValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyProcessShouldBeValidValidatorTest extends TestCase
{
    private ValidatorInterface|MockObject $globalValidator;
    private ExecutionContext|MockObject $context;
    private ContextualValidatorInterface|MockObject $contextualValidator;
    private PropertyProcessShouldBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->globalValidator = $this->createMock(ValidatorInterface::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $this->sut = new PropertyProcessShouldBeValidValidator($this->globalValidator);
        $this->sut->initialize($this->context);
        $this->globalValidator->method('inContext')->with($this->context)->willReturn($this->contextualValidator);
        $this->contextualValidator->method('validate')->willReturn($this->contextualValidator);
        $this->contextualValidator->method('getViolations')->willReturn(new ConstraintViolationList());
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(PropertyProcessShouldBeValidValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['type' => 'no'], new NotBlank());
    }

    public function test_it_should_not_validate_something_else_than_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->contextualValidator->expects($this->never())->method('validate');
        $this->sut->validate(new \stdClass(), new PropertyProcessShouldBeValid());
    }

    public function test_it_should_not_validate_a_process_without_type(): void
    {
        $process = [];
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->contextualValidator->expects($this->never())->method('validate');
        $this->sut->validate($process, new PropertyProcessShouldBeValid());
    }

    public function test_it_should_validate_a_type_no_process(): void
    {
        $process = ['type' => Process::PROCESS_TYPE_NO];
        $constraintArg = null;
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->contextualValidator->expects($this->once())->method('validate')
            ->with($process, $this->isInstanceOf(Collection::class))
            ->willReturnCallback(function ($value, $constraint) use (&$constraintArg) {
                $constraintArg = $constraint;

                return $this->contextualValidator;
            });
        $this->sut->validate($process, new PropertyProcessShouldBeValid());

        // validateProcessTypeNo: Collection with only 'type' field
        $this->assertInstanceOf(Collection::class, $constraintArg);
        $this->assertArrayHasKey('type', $constraintArg->fields);
        $this->assertCount(1, $constraintArg->fields);
    }

    public function test_it_should_validate_a_type_truncate_process(): void
    {
        $process = ['type' => Process::PROCESS_TYPE_TRUNCATE];
        $constraintArg = null;
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->contextualValidator->expects($this->once())->method('validate')
            ->with($process, $this->isInstanceOf(Collection::class))
            ->willReturnCallback(function ($value, $constraint) use (&$constraintArg) {
                $constraintArg = $constraint;

                return $this->contextualValidator;
            });
        $this->sut->validate($process, new PropertyProcessShouldBeValid());

        // validateProcessTypeTruncate: Collection with type, operator, value fields
        $this->assertInstanceOf(Collection::class, $constraintArg);
        $this->assertArrayHasKey('type', $constraintArg->fields);
        $this->assertArrayHasKey('operator', $constraintArg->fields);
        $this->assertArrayHasKey('value', $constraintArg->fields);
        $this->assertCount(3, $constraintArg->fields);
    }

    public function test_it_should_validate_a_type_nomenclature_process(): void
    {
        $process = ['type' => Process::PROCESS_TYPE_NOMENCLATURE];
        $constraintArg = null;
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->contextualValidator->expects($this->once())->method('validate')
            ->with($process, $this->isInstanceOf(Collection::class))
            ->willReturnCallback(function ($value, $constraint) use (&$constraintArg) {
                $constraintArg = $constraint;

                return $this->contextualValidator;
            });
        $this->sut->validate($process, new PropertyProcessShouldBeValid());

        // validateProcessTypeNomenclature: Collection with only 'type' field
        $this->assertInstanceOf(Collection::class, $constraintArg);
        $this->assertArrayHasKey('type', $constraintArg->fields);
        $this->assertCount(1, $constraintArg->fields);
    }

    public function test_it_should_not_validate_an_unknown_type(): void
    {
        $process = ['type' => 'unknown_type'];
        $this->contextualValidator->expects($this->never())->method('validate');
        $this->sut->validate($process, new PropertyProcessShouldBeValid());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Validator\Constraints;

use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectoryValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class WritableDirectoryValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private WritableDirectory|MockObject $constraint;
    private WritableDirectoryValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->constraint = $this->createMock(WritableDirectory::class);
        $this->sut = new WritableDirectoryValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Validator\ConstraintValidator::class, $this->sut);
    }

    public function test_it_does_not_validate_a_null_value(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate(null, $this->constraint);
    }

    public function test_it_invalidates_an_invalid_directory(): void
    {
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->exactly(2))->method('buildViolation')->with($this->constraint->invalidMessage)->willReturn($violation);
        $this->sut->validate([], $this->constraint);
        $this->sut->validate('foo', $this->constraint);
    }

    public function test_it_validates_a_writable_directory(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo', $this->constraint);
        $this->sut->validate(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foo/bar/baz/qux.txt', $this->constraint);
    }

    public function test_it_invalidates_a_non_writable_directory(): void
    {
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->exactly(2))->method('buildViolation')->with($this->constraint->message)->willReturn($violation);
        $this->sut->validate('/foo.csv', $this->constraint);
        $this->sut->validate('/etc/qux/baz/bar/foo.ini', $this->constraint);
    }
}

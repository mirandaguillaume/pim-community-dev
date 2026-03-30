<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DoesImageExistQueryInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\ImageMustExist;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\ImageMustExistValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ImageMustExistValidatorTest extends TestCase
{
    private DoesImageExistQueryInterface|MockObject $imageExistQuery;
    private ExecutionContextInterface|MockObject $context;
    private ImageMustExistValidator $sut;

    protected function setUp(): void
    {
        $this->imageExistQuery = $this->createMock(DoesImageExistQueryInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new ImageMustExistValidator($this->imageExistQuery);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ImageMustExistValidator::class, $this->sut);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_validates_an_image_exist(): void
    {
        $constraint = new ImageMustExist();
        $this->imageExistQuery->method('execute')->with('a/b/c/path.jpg')->willReturn(true);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->assertNull($this->sut->validate('a/b/c/path.jpg', $constraint));
    }

    public function test_it_builds_a_violation_if_the_image_does_not_exist(): void
    {
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new ImageMustExist();
        $this->imageExistQuery->method('execute')->with('not/a/good/path.jpg')->willReturn(false);
        $this->context->expects($this->once())->method('buildViolation')->with('akeneo_connectivity.connection.connection.constraint.image.must_exist')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        $this->assertNull($this->sut->validate('not/a/good/path.jpg', $constraint));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\IsUserOwnerOfTheProduct;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\IsUserOwnerOfTheProductValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsUserOwnerOfTheProductValidatorTest extends TestCase
{
    private GetCategoryCodes|MockObject $getCategoryCodes;
    private GetOwnedCategories|MockObject $getOwnedCategories;
    private GetProductUuids|MockObject $getProductUuids;
    private ExecutionContext|MockObject $context;
    private IsUserOwnerOfTheProductValidator $sut;

    protected function setUp(): void
    {
        $this->getCategoryCodes = $this->createMock(GetCategoryCodes::class);
        $this->getOwnedCategories = $this->createMock(GetOwnedCategories::class);
        $this->getProductUuids = $this->createMock(GetProductUuids::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new IsUserOwnerOfTheProductValidator($this->getCategoryCodes, $this->getOwnedCategories, $this->getProductUuids);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(IsUserOwnerOfTheProductValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_throws_an_exception_with_a_wrong_constraint(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: []
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate($command, new Type([]));
    }

    public function test_it_throws_an_exception_with_a_wrong_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(new \stdClass(), new IsUserOwnerOfTheProduct([]));
    }

    public function test_it_does_nothing_when_product_does_not_exist(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('unknown'),
            userIntents: []
        );
        $this->getProductUuids->expects($this->once())->method('fromIdentifier')->with('unknown')->willReturn(null);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($command, new IsUserOwnerOfTheProduct());
    }

    public function test_it_validates_when_the_product_does_not_have_any_category(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('product_without_category'),
            userIntents: []
        );
        $uuid = Uuid::uuid4();
        $this->getProductUuids->expects($this->once())->method('fromIdentifier')->with('product_without_category')->willReturn($uuid);
        $this->getCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$uuid])->willReturn([$uuid->toString() => []]);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($command, new IsUserOwnerOfTheProduct());
    }

    public function test_it_validates_when_the_product_has_owned_category(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('product_with_category'),
            userIntents: []
        );
        $uuid = Uuid::uuid4();
        $this->getProductUuids->expects($this->once())->method('fromIdentifier')->with('product_with_category')->willReturn($uuid);
        $this->getCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$uuid])->willReturn([$uuid->toString() => ['master', 'print']]);
        $this->getOwnedCategories->method('forUserId')->with(['master', 'print'], 1)->willReturn(['master']);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($command, new IsUserOwnerOfTheProduct());
    }

    public function test_it_adds_a_violation_when_the_product_does_not_have_owned_category(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new IsUserOwnerOfTheProduct();
        $uuid = Uuid::uuid4();
        $command = UpsertProductCommand::createWithUuid(
            userId: 1,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        );
        $this->getCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$uuid])->willReturn([$uuid->toString() => ['master', 'print']]);
        $this->getOwnedCategories->method('forUserId')->with(['master', 'print'], 1)->willReturn([]);
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('userId')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setCode')->with('3')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($command, $constraint);
    }
}

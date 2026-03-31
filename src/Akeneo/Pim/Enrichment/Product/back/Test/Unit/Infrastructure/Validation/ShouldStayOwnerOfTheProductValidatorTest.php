<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuids;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableCategoryCodes;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\ShouldStayOwnerOfTheProduct;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\ShouldStayOwnerOfTheProductValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ShouldStayOwnerOfTheProductValidatorTest extends TestCase
{
    private GetOwnedCategories|MockObject $getOwnedCategories;
    private GetNonViewableCategoryCodes|MockObject $getNonViewableCategoryCodes;
    private GetCategoryCodes|MockObject $getCategoryCodes;
    private GetProductUuids|MockObject $getProductUuids;
    private ExecutionContext|MockObject $context;
    private ShouldStayOwnerOfTheProductValidator $sut;

    protected function setUp(): void
    {
        $this->getOwnedCategories = $this->createMock(GetOwnedCategories::class);
        $this->getNonViewableCategoryCodes = $this->createMock(GetNonViewableCategoryCodes::class);
        $this->getCategoryCodes = $this->createMock(GetCategoryCodes::class);
        $this->getProductUuids = $this->createMock(GetProductUuids::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new ShouldStayOwnerOfTheProductValidator($this->getOwnedCategories, $this->getNonViewableCategoryCodes, $this->getCategoryCodes, $this->getProductUuids);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ShouldStayOwnerOfTheProductValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_only_validates_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(new SetCategories(['foo']), new NotBlank());
    }

    public function test_it_only_validates_category_user_intents(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(new SetGroups(['foo']), new ShouldStayOwnerOfTheProduct());
    }

    public function test_it_does_not_check_add_categories_user_intents(): void
    {
        $this->getProductUuids->expects($this->never())->method('fromIdentifier')->with($this->anything());
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate(new AddCategories(['bar', 'baz']), new ShouldStayOwnerOfTheProduct());
    }

    public function test_it_does_nothing_when_the_product_is_being_created(): void
    {
        $productUuid = Uuid::uuid4();
        $this->context->expects($this->once())->method('getRoot')->willReturn(UpsertProductCommand::createWithUuid(42, ProductUuid::fromUuid($productUuid), []));
        $this->getProductUuids->expects($this->once())->method('fromUuid')->with($productUuid)->willReturn(null);
        $this->getCategoryCodes->expects($this->never())->method('fromProductUuids')->with($this->anything());
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());
    }

    public function test_it_does_nothing_when_the_user_is_not_already_owner_of_the_product(): void
    {
        $productUuid = Uuid::uuid4();
        $this->context->expects($this->once())->method('getRoot')->willReturn(UpsertProductCommand::createWithUuid(
            42,
            ProductUuid::fromUuid($productUuid),
            []
        ));
        $this->getProductUuids->expects($this->once())->method('fromUuid')->with($productUuid)->willReturn($productUuid);
        $this->getCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$productUuid])->willReturn([$productUuid->toString() => ['foo']]);
        $this->getOwnedCategories->expects($this->once())->method('forUserId')->with(['foo'], 42)->willReturn([]);
        $this->getNonViewableCategoryCodes->expects($this->never())->method('fromProductUuids')->with($this->anything());
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate(new SetCategories([]), new ShouldStayOwnerOfTheProduct());
    }

    public function test_it_does_not_raise_any_violation_when_the_user_leaves_an_owned_category(): void
    {
        $uuid = Uuid::uuid4();
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 10,
            productIdentifier: ProductIdentifier::fromIdentifier('my_sku'),
            userIntents: []
        ));
        $this->getProductUuids->expects($this->once())->method('fromIdentifier')->with('my_sku')->willReturn($uuid);
        $this->getCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$uuid])->willReturn([$uuid->toString() => ['categoryA', 'categoryB']]);
        $this->getOwnedCategories->expects($this->once())->method('forUserId')->with(['categoryA', 'categoryB'], 10)->willReturn(['categoryA', 'categoryB']);
        $this->getNonViewableCategoryCodes->expects($this->never())->method('fromProductUuids')->with($this->anything());
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new SetCategories(['categoryA', 'categoryC']), new ShouldStayOwnerOfTheProduct());
    }

    public function test_it_does_not_raise_a_violation_when_the_user_adds_an_owned_category(): void
    {
        $uuid = Uuid::uuid4();
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $this->getProductUuids->expects($this->once())->method('fromUuid')->with($uuid)->willReturn($uuid);
        $this->getCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$uuid])->willReturn([$uuid->toString() => ['categoryA']]);
        $this->getOwnedCategories->expects($this->exactly(2))->method('forUserId')
            ->willReturnCallback(function (array $categories, int $userId) {
                if ($categories === ['categoryA'] && $userId === 10) {
                    return ['categoryA'];
                }
                if ($categories === ['categoryC'] && $userId === 10) {
                    return ['categoryC'];
                }
                return [];
            });
        $this->getNonViewableCategoryCodes->expects($this->never())->method('fromProductUuids')->with($this->anything());
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new SetCategories(['categoryC']), new ShouldStayOwnerOfTheProduct());
    }

    public function test_it_does_not_raise_any_violation_when_the_product_gets_unclassified(): void
    {
        $uuid = Uuid::uuid4();
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $this->getProductUuids->expects($this->once())->method('fromUuid')->with($uuid)->willReturn($uuid);
        $this->getCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$uuid])->willReturn([$uuid->toString() => ['categoryA', 'categoryB']]);
        $this->getOwnedCategories->expects($this->once())->method('forUserId')->with(['categoryA', 'categoryB'], 10)->willReturn(['categoryA', 'categoryB']);
        $this->getNonViewableCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$uuid], 10)->willReturn([$uuid->toString() => []]);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new RemoveCategories(['categoryA', 'categoryB']), new ShouldStayOwnerOfTheProduct());
    }

    public function test_it_adds_a_violation_when_the_user_replaces_all_owned_categories(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new ShouldStayOwnerOfTheProduct();
        $uuid = Uuid::uuid4();
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $this->getProductUuids->expects($this->once())->method('fromUuid')->with($uuid)->willReturn($uuid);
        $this->getCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$uuid])->willReturn(['categoryA', 'categoryB']);
        $this->getOwnedCategories->expects($this->exactly(2))->method('forUserId')
            ->willReturnCallback(function (array $categories, int $userId) {
                if ($categories === ['categoryA', 'categoryB'] && $userId === 10) {
                    return ['categoryA'];
                }
                if ($categories === ['categoryB', 'categoryC'] && $userId === 10) {
                    return [];
                }
                return [];
            });
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($violationBuilder);
        $violationBuilder->method('setCode')->with((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(new SetCategories(['categoryB', 'categoryC']), $constraint);
    }

    public function test_it_adds_a_violation_when_the_user_removes_all_owned_categories(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new ShouldStayOwnerOfTheProduct();
        $uuid = Uuid::uuid4();
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $this->getProductUuids->expects($this->once())->method('fromUuid')->with($uuid)->willReturn($uuid);
        $this->getCategoryCodes->method('fromProductUuids')->with([$uuid])->willReturn([$uuid->toString() => ['categoryA', 'categoryB', 'categoryC']]);
        $this->getOwnedCategories->method('forUserId')
            ->willReturnCallback(function (array $categories, int $userId) {
                if ($categories === ['categoryA', 'categoryB', 'categoryC'] && $userId === 10) {
                    return ['categoryB'];
                }
                if ($categories === ['categoryA', 'categoryC'] && $userId === 10) {
                    return [];
                }
                return [];
            });
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($violationBuilder);
        $violationBuilder->method('setCode')->with((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(new RemoveCategories(['categoryB']), $constraint);
    }

    public function test_it_adds_a_violation_when_all_viewable_categories_are_removed(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new ShouldStayOwnerOfTheProduct();
        $uuid = Uuid::uuid4();
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithUuid(
            userId: 10,
            productUuid: ProductUuid::fromUuid($uuid),
            userIntents: []
        ));
        $this->getProductUuids->expects($this->once())->method('fromUuid')->with($uuid)->willReturn($uuid);
        $this->getCategoryCodes->method('fromProductUuids')->with([$uuid])->willReturn([$uuid->toString() => ['categoryA', 'categoryB']]);
        $this->getOwnedCategories->method('forUserId')->with(['categoryA', 'categoryB'], 10)->willReturn(['categoryB']);
        $this->getNonViewableCategoryCodes->expects($this->once())->method('fromProductUuids')->with([$uuid], 10)->willReturn([$uuid->toString() => ['non_viewable_category']]);
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($violationBuilder);
        $violationBuilder->method('setCode')->with((string) ViolationCode::PERMISSION)->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(new RemoveCategories(['categoryA', 'categoryB']), $constraint);
    }
}

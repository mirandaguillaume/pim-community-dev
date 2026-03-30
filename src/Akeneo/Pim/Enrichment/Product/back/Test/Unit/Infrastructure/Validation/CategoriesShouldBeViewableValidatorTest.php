<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Category\API\Query\GetViewableCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\CategoriesShouldBeViewable;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\CategoriesShouldBeViewableValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CategoriesShouldBeViewableValidatorTest extends TestCase
{
    private GetViewableCategories|MockObject $getViewableCategories;
    private ExecutionContext|MockObject $context;
    private CategoriesShouldBeViewableValidator $sut;

    protected function setUp(): void
    {
        $this->getViewableCategories = $this->createMock(GetViewableCategories::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new CategoriesShouldBeViewableValidator($this->getViewableCategories);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(CategoriesShouldBeViewableValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_throws_an_exception_with_a_wrong_constraint(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(userId: 1, productIdentifier: ProductIdentifier::fromIdentifier('foo'), userIntents: []);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate($command, new Type([]));
    }

    public function test_it_throws_an_exception_with_a_wrong_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(new \stdClass(), new CategoriesShouldBeViewable([]));
    }

    public function test_it_allows_adding_categories_if_user_has_access(): void
    {
        $categoryUserIntent = new SetCategories(['master', 'print', 'ecommerce']);
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: [$categoryUserIntent]
        ));
        $this->getViewableCategories->method('forUserId')->with(['master', 'print', 'ecommerce'], 1)->willReturn(['master', 'print', 'ecommerce']);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($categoryUserIntent, new CategoriesShouldBeViewable());
    }

    public function test_it_adds_a_violation_when_a_category_is_not_viewable(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $categoryUserIntent = new SetCategories(['master', 'print', 'ecommerce']);
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: [$categoryUserIntent]
        ));
        $this->getViewableCategories->method('forUserId')->with(['master', 'print', 'ecommerce'], 1)->willReturn(['master', 'ecommerce']);
        $this->context->expects($this->once())->method('buildViolation')->with(
            'pim_enrich.product.validation.upsert.category_does_not_exist',
            ['{{ categoryCodes }}' => 'print', '%count%' => 1]
        )->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($categoryUserIntent, new CategoriesShouldBeViewable());
    }

    public function test_it_adds_a_violation_when_several_categories_are_not_viewable(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $categoryUserIntent = new SetCategories(['master', 'print', 'ecommerce', 'print']);
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: [$categoryUserIntent]
        ));
        $this->getViewableCategories->method('forUserId')->with(['master', 'print', 'ecommerce'], 1)->willReturn(['master']);
        $this->context->expects($this->once())->method('buildViolation')->with(
            'pim_enrich.product.validation.upsert.category_does_not_exist',
            ['{{ categoryCodes }}' => 'print, ecommerce', '%count%' => 2]
        )->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($categoryUserIntent, new CategoriesShouldBeViewable());
    }
}

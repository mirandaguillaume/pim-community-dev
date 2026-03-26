<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Category\ServiceApi\Category;
use Akeneo\Category\ServiceApi\CategoryQueryInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\CategoryCodesShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\CategoryCodesShouldExistValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryCodesShouldExistValidatorTest extends TestCase
{
    private CategoryQueryInterface|MockObject $categoryQuery;
    private ExecutionContext|MockObject $executionContext;
    private CategoryCodesShouldExistValidator $sut;

    protected function setUp(): void
    {
        $this->categoryQuery = $this->createMock(CategoryQueryInterface::class);
        $this->executionContext = $this->createMock(ExecutionContext::class);
        $this->sut = new CategoryCodesShouldExistValidator($this->categoryQuery);
        $this->sut->initialize($this->executionContext);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CategoryCodesShouldExistValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['type' => 'category', 'operator' => 'IN', 'value' => ['shirts']], new NotBlank());
    }

    public function test_it_should_not_validate_if_category_codes_is_not_an_array(): void
    {
        $categoryCodes = 'foo';
        $this->executionContext->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    public function test_it_should_not_validate_if_category_codes_is_empty(): void
    {
        $categoryCodes = [];
        $this->executionContext->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    public function test_it_should_not_validate_if_category_codes_is_not_an_array_of_strings(): void
    {
        $categoryCodes = ['shirts', true];
        $this->executionContext->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    public function test_it_should_not_build_violation_if_categories_exist(): void
    {
        $categoryCodes = ['shirts'];
        $shirtCategory = new Category(1, 'shirts');
        $this->categoryQuery->expects($this->once())->method('byCodes')->with($categoryCodes)->willReturn($this->arrayAsGenerator([$shirtCategory]));
        $this->executionContext->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    public function test_it_should_build_violation_if_categories_do_not_exist(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $categoryCodes = ['shirts', 'unknown_category1', 'unknown_category2'];
        $shirtCategory = new Category(1, 'shirts');
        $this->categoryQuery->expects($this->once())->method('byCodes')->with($categoryCodes)->willReturn($this->arrayAsGenerator([$shirtCategory]));
        $this->executionContext->expects($this->once())->method('buildViolation')->with(
            'validation.identifier_generator.categories_do_not_exist',
            ['{{ categoryCodes }}' => '"unknown_category1", "unknown_category2"']
        )->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($categoryCodes, new CategoryCodesShouldExist());
    }

    private function arrayAsGenerator(array $array): \Generator
    {
        foreach ($array as $item) {
            yield $item;
        }
    }
}

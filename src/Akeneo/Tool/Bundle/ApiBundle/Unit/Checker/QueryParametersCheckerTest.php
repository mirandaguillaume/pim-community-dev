<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ApiBundle\Checker;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersChecker;
use Akeneo\Tool\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class QueryParametersCheckerTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $localeRepository;
    private IdentifiableObjectRepositoryInterface|MockObject $attributeRepository;
    private IdentifiableObjectRepositoryInterface|MockObject $categoryRepository;
    private QueryParametersChecker $sut;

    protected function setUp(): void
    {
        $this->localeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->attributeRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->categoryRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new QueryParametersChecker(
            $this->localeRepository,
            $this->attributeRepository,
            $this->categoryRepository,
            ['family', 'enabled', 'groups', 'categories', 'completeness']
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(QueryParametersChecker::class, $this->sut);
    }

    public function test_it_should_be_a_query_param_checker(): void
    {
        $this->assertInstanceOf(QueryParametersCheckerInterface::class, $this->sut);
    }

    public function test_it_raises_an_exception_if_a_locale_does_not_exist(): void
    {
        $enUsLocale = $this->createMock(LocaleInterface::class);

        $localeCodes = ['de_DE', 'en_US'];
        $enUsLocale->method('isActivated')->willReturn(true);
        $this->localeRepository->method('findOneByIdentifier')->willReturnCallback(
            function (string $identifier) use ($enUsLocale) {
                return match ($identifier) {
                    'de_DE' => null,
                    'en_US' => $enUsLocale,
                    default => null,
                };
            }
        );
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Locale "de_DE" does not exist or is not activated.');
        $this->sut->checkLocalesParameters($localeCodes, null);
    }

    public function test_it_raises_an_exception_if_a_locale_is_not_activated(): void
    {
        $enUsLocale = $this->createMock(LocaleInterface::class);

        $localeCodes = ['de_DE', 'en_US'];
        $enUsLocale->method('isActivated')->willReturn(false);
        $this->localeRepository->method('findOneByIdentifier')->willReturnCallback(
            function (string $identifier) use ($enUsLocale) {
                return match ($identifier) {
                    'de_DE' => null,
                    'en_US' => $enUsLocale,
                    default => null,
                };
            }
        );
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Locales "de_DE, en_US" do not exist or are not activated.');
        $this->sut->checkLocalesParameters($localeCodes, null);
    }

    public function test_it_raises_an_exception_if_locales_do_not_exist(): void
    {
        $localeCodes = ['de_DE', 'en_US'];
        $this->localeRepository->method('findOneByIdentifier')->willReturn(null);
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Locales "de_DE, en_US" do not exist or are not activated.');
        $this->sut->checkLocalesParameters($localeCodes, null);
    }

    public function test_it_should_not_raise_an_exception_if_a_locale_exist(): void
    {
        $enUsLocale = $this->createMock(LocaleInterface::class);
        $deDeLocale = $this->createMock(LocaleInterface::class);
        $enUsLocale->method('isActivated')->willReturn(true);
        $deDeLocale->method('isActivated')->willReturn(true);

        $localeCodes = ['de_DE', 'en_US'];
        $this->localeRepository->method('findOneByIdentifier')->willReturnCallback(
            function (string $identifier) use ($deDeLocale, $enUsLocale) {
                return match ($identifier) {
                    'de_DE' => $deDeLocale,
                    'en_US' => $enUsLocale,
                    default => null,
                };
            }
        );
        $this->sut->checkLocalesParameters($localeCodes, null);
        $this->addToAssertionCount(1);
    }

    public function test_it_raises_an_exception_if_an_attribute_does_not_exist(): void
    {
        $attribute1 = $this->createMock(AttributeInterface::class);

        $attributeCodes = ['attribute_1', 'attribute_2'];
        $this->attributeRepository->method('findOneByIdentifier')->willReturnCallback(
            function (string $identifier) use ($attribute1) {
                return match ($identifier) {
                    'attribute_1' => $attribute1,
                    'attribute_2' => null,
                    default => null,
                };
            }
        );
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Attribute "attribute_2" does not exist.');
        $this->sut->checkAttributesParameters($attributeCodes);
    }

    public function test_it_raises_an_exception_if_attributes_do_not_exist(): void
    {
        $attributeCodes = ['attribute_1', 'attribute_2'];
        $this->attributeRepository->method('findOneByIdentifier')->willReturn(null);
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Attributes "attribute_1, attribute_2" do not exist.');
        $this->sut->checkAttributesParameters($attributeCodes);
    }

    public function test_it_should_not_raise_an_exception_if_attribute_exist(): void
    {
        $attribute1 = $this->createMock(AttributeInterface::class);
        $attribute2 = $this->createMock(AttributeInterface::class);

        $attributeCodes = ['attribute_1', 'attribute_2'];
        $this->attributeRepository->method('findOneByIdentifier')->willReturnCallback(
            function (string $identifier) use ($attribute1, $attribute2) {
                return match ($identifier) {
                    'attribute_1' => $attribute1,
                    'attribute_2' => $attribute2,
                    default => null,
                };
            }
        );
        $this->sut->checkAttributesParameters($attributeCodes);
        $this->addToAssertionCount(1);
    }

    public function test_it_raises_an_exception_if_a_category_does_not_exist(): void
    {
        $category1 = $this->createMock(CategoryInterface::class);

        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];
        $this->categoryRepository->method('findOneByIdentifier')->willReturnCallback(
            function (string $identifier) use ($category1) {
                return match ($identifier) {
                    'category_1' => $category1,
                    'category_2' => null,
                    default => null,
                };
            }
        );
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Category "category_2" does not exist.');
        $this->sut->checkCategoriesParameters($categories);
    }

    public function test_it_raises_an_exception_if_categories_do_not_exist(): void
    {
        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];
        $this->categoryRepository->method('findOneByIdentifier')->willReturn(null);
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Categories "category_1, category_2" do not exist.');
        $this->sut->checkCategoriesParameters($categories);
    }

    public function test_it_should_not_raise_an_exception_if_a_category_exist(): void
    {
        $category1 = $this->createMock(CategoryInterface::class);
        $category2 = $this->createMock(CategoryInterface::class);

        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];
        $this->categoryRepository->method('findOneByIdentifier')->willReturnCallback(
            function (string $identifier) use ($category1, $category2) {
                return match ($identifier) {
                    'category_1' => $category1,
                    'category_2' => $category2,
                    default => null,
                };
            }
        );
        $this->sut->checkCategoriesParameters($categories);
        $this->addToAssertionCount(1);
    }

    public function test_it_should_throw_an_exception_if_json_is_null(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->expectExceptionMessage('Search query parameter should be valid JSON.');
        $this->sut->checkCriterionParameters('');
    }

    public function test_it_should_throw_an_exception_if_it_is_not_correctly_structured(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);

        $this->expectExceptionMessage('Structure of filter "categories" should respect this structure: {"categories":[{"operator": "my_operator", "value": "my_value"}]}');
        $this->sut->checkCriterionParameters('{"categories":[]}');
    }

    public function test_it_should_throw_an_exception_if_operator_is_missing(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);

        $this->expectExceptionMessage('Operator is missing for the property "categories".');
        $this->sut->checkCriterionParameters('{"categories":[{"value": "my_value"}]}');
    }

    public function test_it_should_throw_an_exception_if_property_is_not_a_product_filter_or_an_attribute(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);

        $this->expectExceptionMessage('Filter on property "wrong_attribute" is not supported or does not support operator "my_operator"');
        $this->sut->checkPropertyParameters('wrong_attribute', 'my_operator');
    }
}

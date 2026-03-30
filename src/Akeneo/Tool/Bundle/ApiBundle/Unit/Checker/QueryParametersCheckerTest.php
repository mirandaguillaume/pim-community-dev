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
        $this->localeRepository->method('findOneByIdentifier')->with('de_DE')->willReturn(null);
        $this->localeRepository->method('findOneByIdentifier')->with('en_US')->willReturn($enUsLocale);
        $this->expectException(new UnprocessableEntityHttpException('Locale "de_DE" does not exist or is not activated.'));
        $this->sut->checkLocalesParameters($localeCodes, null);
    }

    public function test_it_raises_an_exception_if_a_locale_is_not_activated(): void
    {
        $enUsLocale = $this->createMock(LocaleInterface::class);

        $localeCodes = ['de_DE', 'en_US'];
        $enUsLocale->method('isActivated')->willReturn(false);
        $this->localeRepository->method('findOneByIdentifier')->with('de_DE')->willReturn(null);
        $this->localeRepository->method('findOneByIdentifier')->with('en_US')->willReturn($enUsLocale);
        $this->expectException(new UnprocessableEntityHttpException('Locales "de_DE, en_US" do not exist or are not activated.'));
        $this->sut->checkLocalesParameters($localeCodes, null);
    }

    public function test_it_raises_an_exception_if_locales_do_not_exist(): void
    {
        $localeCodes = ['de_DE', 'en_US'];
        $this->localeRepository->method('findOneByIdentifier')->with('de_DE')->willReturn(null);
        $this->localeRepository->method('findOneByIdentifier')->with('en_US')->willReturn(null);
        $this->expectException(new UnprocessableEntityHttpException('Locales "de_DE, en_US" do not exist or are not activated.'));
        $this->sut->checkLocalesParameters($localeCodes, null);
    }

    public function test_it_should_not_raise_an_exception_if_a_locale_exist(): void
    {
        $enUsLocale = $this->createMock(LocaleInterface::class);
        $deDeLocale = $this->createMock(LocaleInterface::class);

        $localeCodes = ['de_DE', 'en_US'];
        $this->localeRepository->method('findOneByIdentifier')->with('de_DE')->willReturn($deDeLocale);
        $this->localeRepository->method('findOneByIdentifier')->with('en_US')->willReturn($enUsLocale);
        $this->sut->shouldNotThrow('UnprocessableEntityHttpException')
                    ->during('checkLocalesParameters', [$localeCodes, null]);
    }

    public function test_it_raises_an_exception_if_an_attribute_does_not_exist(): void
    {
        $attribute1 = $this->createMock(AttributeInterface::class);
        $attribute2 = $this->createMock(AttributeInterface::class);
        $attributeGroup1 = $this->createMock(AttributeGroupInterface::class);
        $attributeGroup2 = $this->createMock(AttributeGroupInterface::class);

        $attributeCodes = ['attribute_1', 'attribute_2'];
        $attribute1->method('getGroup')->willReturn($attributeGroup1);
        $attribute2->method('getGroup')->willReturn($attributeGroup2);
        $this->attributeRepository->method('findOneByIdentifier')->with('attribute_1')->willReturn($attribute1);
        $this->attributeRepository->method('findOneByIdentifier')->with('attribute_2')->willReturn(null);
        $this->expectException(new UnprocessableEntityHttpException('Attribute "attribute_2" does not exist.'));
        $this->sut->checkAttributesParameters($attributeCodes);
    }

    public function test_it_raises_an_exception_if_attributes_do_not_exist(): void
    {
        $attribute1 = $this->createMock(AttributeInterface::class);
        $attribute2 = $this->createMock(AttributeInterface::class);
        $attributeGroup1 = $this->createMock(AttributeGroupInterface::class);
        $attributeGroup2 = $this->createMock(AttributeGroupInterface::class);

        $attributeCodes = ['attribute_1', 'attribute_2'];
        $attribute1->method('getGroup')->willReturn($attributeGroup1);
        $attribute2->method('getGroup')->willReturn($attributeGroup2);
        $this->attributeRepository->method('findOneByIdentifier')->with('attribute_1')->willReturn(null);
        $this->attributeRepository->method('findOneByIdentifier')->with('attribute_2')->willReturn(null);
        $this->expectException(new UnprocessableEntityHttpException('Attributes "attribute_1, attribute_2" do not exist.'));
        $this->sut->checkAttributesParameters($attributeCodes);
    }

    public function test_it_should_not_raise_an_exception_if_attribute_exist(): void
    {
        $attribute1 = $this->createMock(AttributeInterface::class);
        $attribute2 = $this->createMock(AttributeInterface::class);
        $attributeGroup1 = $this->createMock(AttributeGroupInterface::class);
        $attributeGroup2 = $this->createMock(AttributeGroupInterface::class);

        $attributeCodes = ['attribute_1', 'attribute_2'];
        $attribute1->method('getGroup')->willReturn($attributeGroup1);
        $attribute2->method('getGroup')->willReturn($attributeGroup2);
        $this->attributeRepository->method('findOneByIdentifier')->with('attribute_1')->willReturn($attribute1);
        $this->attributeRepository->method('findOneByIdentifier')->with('attribute_2')->willReturn($attribute2);
        $this->sut->shouldNotThrow('UnprocessableEntityHttpException')
                    ->during('checkAttributesParameters', [$attributeCodes]);
    }

    public function test_it_raises_an_exception_if_a_category_does_not_exist(): void
    {
        $category1 = $this->createMock(CategoryInterface::class);
        $category2 = $this->createMock(CategoryInterface::class);

        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];
        $category1->method('getCode')->willReturn('category_1');
        $category2->method('getCode')->willReturn('category_2');
        $this->categoryRepository->method('findOneByIdentifier')->with('category_1')->willReturn($category1);
        $this->categoryRepository->method('findOneByIdentifier')->with('category_2')->willReturn(null);
        $this->expectException(new UnprocessableEntityHttpException('Category "category_2" does not exist.'));
        $this->sut->checkCategoriesParameters($categories);
    }

    public function test_it_raises_an_exception_if_categories_do_not_exist(): void
    {
        $category1 = $this->createMock(CategoryInterface::class);
        $category2 = $this->createMock(CategoryInterface::class);

        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];
        $category1->method('getCode')->willReturn('category_1');
        $category2->method('getCode')->willReturn('category_2');
        $this->categoryRepository->method('findOneByIdentifier')->with('category_1')->willReturn(null);
        $this->categoryRepository->method('findOneByIdentifier')->with('category_2')->willReturn(null);
        $this->expectException(new UnprocessableEntityHttpException('Categories "category_1, category_2" do not exist.'));
        $this->sut->checkCategoriesParameters($categories);
    }

    public function test_it_should_not_raise_an_exception_if_a_category_exist(): void
    {
        $category1 = $this->createMock(CategoryInterface::class);
        $category2 = $this->createMock(CategoryInterface::class);

        $categories = [['value' => ['category_1']], ['value' => ['category_2']]];
        $category1->method('getCode')->willReturn('category_1');
        $category2->method('getCode')->willReturn('category_2');
        $this->categoryRepository->method('findOneByIdentifier')->with('category_1')->willReturn($category1);
        $this->categoryRepository->method('findOneByIdentifier')->with('category_2')->willReturn($category2);
        $this->sut->shouldNotThrow('UnprocessableEntityHttpException')
                    ->during('checkCategoriesParameters', [$categories]);
    }

    public function test_it_should_throw_an_exception_if_json_is_null(): void
    {
        $this->expectException(new BadRequestHttpException('Search query parameter should be valid JSON.'));
        $this->sut->checkCriterionParameters('');
    }

    public function test_it_should_throw_an_exception_if_it_is_not_correctly_structured(): void
    {
        $this->expectException(new UnprocessableEntityHttpException('Structure of filter "categories" should respect this structure: {"categories":[{"operator": "my_operator", "value": "my_value"}]}'));
        $this->sut->checkCriterionParameters('{"categories":[]}');
    }

    public function test_it_should_throw_an_exception_if_operator_is_missing(): void
    {
        $this->expectException(new UnprocessableEntityHttpException('Operator is missing for the property "categories".'));
        $this->sut->checkCriterionParameters('{"categories":[{"value": "my_value"}]}');
    }

    public function test_it_should_throw_an_exception_if_property_is_not_a_product_filter_or_an_attribute(): void
    {
        $this->expectException(new UnprocessableEntityHttpException('Filter on property "wrong_attribute" is not supported or does not support operator "my_operator"'));
        $this->sut->checkPropertyParameters('wrong_attribute', 'my_operator');
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValuesCollectionTest extends TestCase
{
    private ProductValuesCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductValuesCollection();
    }

    public function test_it_returns_the_product_values_for_attributes_of_type_text(): void
    {
        $attributeText1 = $this->givenALocalizableAttributeOfTypeText('text_1');
        $attributeText2 = $this->givenANotLocalizableAttributeOfTypeText('text_2');
        $attributeTextarea = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea');
        $textValues1 = $this->givenRandomValuesForAttribute($attributeText1);
        $textValues2 = $this->givenRandomValuesForAttribute($attributeText2);
        $textareaValues = $this->givenRandomValuesForAttribute($attributeTextarea);
        $this->sut->add($textValues1);
        $this->sut->add($textValues2);
        $this->sut->add($textareaValues);
        $allTextValues = iterator_to_array($this->getTextValues());
        Assert::eq($allTextValues, [$textValues1, $textValues2]);
    }

    public function test_it_returns_the_product_values_for_attributes_of_type_textarea(): void
    {
        $attributeTextarea1 = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea_1');
        $attributeTextarea2 = $this->givenANotLocalizableAttributeOfTypeTextarea('a_textarea_2');
        $attributeText = $this->givenALocalizableAttributeOfTypeText('a_text');
        $textareaValues1 = $this->givenRandomValuesForAttribute($attributeTextarea1);
        $textareaValues2 = $this->givenRandomValuesForAttribute($attributeTextarea2);
        $textValues = $this->givenRandomValuesForAttribute($attributeText);
        $this->sut->add($textareaValues1);
        $this->sut->add($textareaValues2);
        $this->sut->add($textValues);
        $allTextValues = iterator_to_array($this->getTextareaValues());
        Assert::eq($allTextValues, [$textareaValues1, $textareaValues2]);
    }

    public function test_it_returns_the_product_values_for_localizable_attributes_of_type_text(): void
    {
        $localizableAttributeText1 = $this->givenALocalizableAttributeOfTypeText('localizable_text_1');
        $localizableAttributeText2 = $this->givenALocalizableAttributeOfTypeText('localizable_text_2');
        $notLocalizableAttributeText = $this->givenANotLocalizableAttributeOfTypeText('not_localizable_text');
        $attributeTextarea = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea');
        $localizableTextValues1 = $this->givenRandomValuesForAttribute($localizableAttributeText1);
        $localizableTextValues2 = $this->givenRandomValuesForAttribute($localizableAttributeText2);
        $notLocalizableTextValues = $this->givenRandomValuesForAttribute($notLocalizableAttributeText);
        $textareaValues = $this->givenRandomValuesForAttribute($attributeTextarea);
        $this->sut->add($localizableTextValues1);
        $this->sut->add($localizableTextValues2);
        $this->sut->add($notLocalizableTextValues);
        $this->sut->add($textareaValues);
        $allTextValues = iterator_to_array($this->getLocalizableTextValues());
        Assert::eq($allTextValues, [$localizableTextValues1, $localizableTextValues2]);
    }

    public function test_it_returns_the_product_values_for_localizable_attributes_of_type_textarea(): void
    {
        $localizableAttributeTextarea1 = $this->givenALocalizableAttributeOfTypeTextarea('localizable_textarea_1');
        $localizableAttributeTextarea2 = $this->givenALocalizableAttributeOfTypeTextarea('localizable_textarea_2');
        $notLocalizableAttributeText = $this->givenANotLocalizableAttributeOfTypeTextarea('not_localizable_textarea');
        $attributeText = $this->givenALocalizableAttributeOfTypeText('a_text');
        $localizableTextareaValues1 = $this->givenRandomValuesForAttribute($localizableAttributeTextarea1);
        $localizableTextareaValues2 = $this->givenRandomValuesForAttribute($localizableAttributeTextarea2);
        $notLocalizableTextareaValues = $this->givenRandomValuesForAttribute($notLocalizableAttributeText);
        $textValues = $this->givenRandomValuesForAttribute($attributeText);
        $this->sut->add($localizableTextareaValues1);
        $this->sut->add($localizableTextareaValues2);
        $this->sut->add($notLocalizableTextareaValues);
        $this->sut->add($textValues);
        $allTextValues = iterator_to_array($this->getLocalizableTextareaValues());
        Assert::eq($allTextValues, [$localizableTextareaValues1, $localizableTextareaValues2]);
    }

    private function givenALocalizableAttributeOfTypeText(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), true);
    }

    private function givenANotLocalizableAttributeOfTypeText(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), false);
    }

    private function givenALocalizableAttributeOfTypeTextarea(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::textarea(), true);
    }

    private function givenANotLocalizableAttributeOfTypeTextarea(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::textarea(), false);
    }

    private function givenRandomValuesForAttribute(Attribute $attribute): ProductValues
    {
        $values = (new ChannelLocaleDataCollection())
            ->addToChannelAndLocale(new ChannelCode('mobile'), new LocaleCode('en_US'), strval(Uuid::uuid4()))
            ->addToChannelAndLocale(new ChannelCode('print'), new LocaleCode('fr_FR'), strval(Uuid::uuid4()));
    
        return new ProductValues($attribute, $values);
    }
}

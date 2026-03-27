<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeCollectionTest extends TestCase
{
    private AttributeCollection $sut;

    protected function setUp(): void {}

    public function test_it_retrieves_an_attribute_from_identifier(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(1);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(2);
        $mainImageAttribute = $this->createMainImageImageAttribute(3);
        $this->sut = AttributeCollection::fromArray([$shortDescriptionAttribute, $longDescriptionAttribute, $mainImageAttribute], );
        $this->assertSame($mainImageAttribute, $this->sut->getAttributeByIdentifier('main_image|d049da25-5f74-43ba-b261-65ee2c9dc9f4'));
    }

    public function test_it_retrieves_an_attribute_from_code(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(1);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(2);
        $mainImageAttribute = $this->createMainImageImageAttribute(3);
        $this->sut = AttributeCollection::fromArray([$shortDescriptionAttribute, $longDescriptionAttribute, $mainImageAttribute], );
        $this->assertSame($shortDescriptionAttribute, $this->sut->getAttributeByCode('short_description'));
    }

    public function test_it_adds_a_new_attribute_to_its_attributes_list(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(1);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(2);
        $mainImageAttribute = $this->createMainImageImageAttribute(3);
        $this->sut = AttributeCollection::fromArray([$shortDescriptionAttribute, $longDescriptionAttribute, $mainImageAttribute], );
        $newAttribute = AttributeTextArea::create(
            AttributeUuid::fromString('f54102b9-a801-4d97-ae51-916450972c07'),
            new AttributeCode('new_attribute'),
            AttributeOrder::fromInteger(9),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(true),
            LabelCollection::fromArray(["en_US" => "New attribute"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );
        $this->sut->addAttribute($newAttribute);
        $this->assertSame($newAttribute, $this->sut->getAttributeByCode('new_attribute'));
    }

    public function test_it_counts_its_number_of_attributes(): void
    {
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(2);
        $mainImageAttribute = $this->createMainImageImageAttribute(3);
        $this->sut = AttributeCollection::fromArray([$longDescriptionAttribute, $mainImageAttribute], );
        $this->assertSame(2, $this->sut->count());
    }

    public function test_it_reindexes_its_attributes(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(30);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(50);
        $mainImageAttribute = $this->createMainImageImageAttribute(20);
        $attributeWithDuplicatedOrderIndex = AttributeText::create(
            AttributeUuid::fromString('d15245be-7d71-40e0-9638-d9f1b2bb3f5f'),
            new AttributeCode('duplicated_order'),
            AttributeOrder::fromInteger(30),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsScopable::fromBoolean(false),
            AttributeIsLocalizable::fromBoolean(false),
            LabelCollection::fromArray(["en_US" => "Duplicated order"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );
        $this->sut = AttributeCollection::fromArray([$shortDescriptionAttribute, $longDescriptionAttribute, $attributeWithDuplicatedOrderIndex, $mainImageAttribute], );
        $reindexedAttributeCollection = $this->sut->rebuildWithIndexedAttributes();
        $this->assertSame(1, $reindexedAttributeCollection->getAttributeByCode('main_image')->getOrder()->intValue());
        $this->assertSame(2, $reindexedAttributeCollection->getAttributeByCode('short_description')->getOrder()->intValue());
        $this->assertSame(3, $reindexedAttributeCollection->getAttributeByCode('duplicated_order')->getOrder()->intValue());
        $this->assertSame(4, $reindexedAttributeCollection->getAttributeByCode('long_description')->getOrder()->intValue());
    }

    public function test_it_returns_the_potential_order_value_of_the_next_added_attribute(): void
    {
        $shortDescriptionAttribute = $this->createShortDescriptionTextAttribute(1);
        $longDescriptionAttribute = $this->createLongDescriptionTextAttribute(27);
        $mainImageAttribute = $this->createMainImageImageAttribute(50);
        $this->sut = AttributeCollection::fromArray([$shortDescriptionAttribute, $longDescriptionAttribute, $mainImageAttribute], );
        $this->assertSame(3, $this->sut->count());
        $this->assertSame(51, $this->sut->calculateNextOrder());
    }

    public function test_calculate_next_order_with_empty_collection(): void
    {
        $this->sut = AttributeCollection::fromArray([]);
        // With no attributes, the reduce returns initial value 1, so next order = 1 + 1 = 2
        $this->assertSame(2, $this->sut->calculateNextOrder());
    }

    public function test_calculate_next_order_with_single_attribute(): void
    {
        $attr = $this->createShortDescriptionTextAttribute(5);
        $this->sut = AttributeCollection::fromArray([$attr]);
        // max(5, 1) = 5, so next = 1 + 5 = 6
        $this->assertSame(6, $this->sut->calculateNextOrder());
    }

    public function test_calculate_next_order_is_one_plus_max(): void
    {
        $attr1 = $this->createShortDescriptionTextAttribute(3);
        $attr2 = $this->createLongDescriptionTextAttribute(10);
        $this->sut = AttributeCollection::fromArray([$attr1, $attr2]);
        // max(3, 10) = 10, so next = 1 + 10 = 11
        $this->assertSame(11, $this->sut->calculateNextOrder());
    }

    public function test_it_rejects_non_attribute_in_constructor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AttributeCollection::fromArray(['not_an_attribute']);
    }

    public function test_normalize_returns_array_of_normalized_attributes(): void
    {
        $attr = $this->createShortDescriptionTextAttribute(1);
        $this->sut = AttributeCollection::fromArray([$attr]);
        $normalized = $this->sut->normalize();
        $this->assertIsArray($normalized);
        $this->assertCount(1, $normalized);
        $this->assertArrayHasKey('uuid', $normalized[0]);
        $this->assertArrayHasKey('code', $normalized[0]);
        $this->assertSame('short_description', $normalized[0]['code']);
    }

    public function test_get_attributes_returns_array(): void
    {
        $attr = $this->createShortDescriptionTextAttribute(1);
        $this->sut = AttributeCollection::fromArray([$attr]);
        $this->assertCount(1, $this->sut->getAttributes());
        $this->assertSame($attr, $this->sut->getAttributes()[0]);
    }

    private function createShortDescriptionTextAttribute(int $order): AttributeText
    {
        return AttributeText::create(
            AttributeUuid::fromString('e30177ee-d8e8-46a4-9491-ea6c3579e727'),
            new AttributeCode('short_description'),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsScopable::fromBoolean(false),
            AttributeIsLocalizable::fromBoolean(false),
            LabelCollection::fromArray(["en_US" => "Short description"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );
    }

    private function createLongDescriptionTextAttribute(int $order): AttributeText
    {
        return AttributeText::create(
            AttributeUuid::fromString('82afa0d1-cf51-48e0-a8d3-34444ddc1c09'),
            new AttributeCode('long_description'),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsScopable::fromBoolean(true),
            AttributeIsLocalizable::fromBoolean(false),
            LabelCollection::fromArray(['en_US' => "Long description"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );
    }

    private function createMainImageImageAttribute(int $order): AttributeImage
    {
        return AttributeImage::create(
            AttributeUuid::fromString('d049da25-5f74-43ba-b261-65ee2c9dc9f4'),
            new AttributeCode('main_image'),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsScopable::fromBoolean(false),
            AttributeIsLocalizable::fromBoolean(false),
            LabelCollection::fromArray(['en_US' => "Illustration"]),
            TemplateUuid::fromString('b60bb301-33e3-43ef-8a2c-a95361b607c2'),
            AttributeAdditionalProperties::fromArray([])
        );
    }
}

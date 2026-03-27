<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\Model\Enrichment;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryTest extends TestCase
{
    private Category $sut;

    protected function setUp(): void
    {
        $this->sut = new Category(
            new CategoryId(1),
            new Code('my_category'),
            TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            LabelCollection::fromArray(['fr_FR' => 'category_libelle']),
            null,
            null,
            new CategoryId(1),
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2021-03-24 16:00:00'),
            ValueCollection::fromArray([TextValue::fromApplier(
                value: 'Meta shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            )]),
            PermissionCollection::fromArray(
                [
                    "view" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                    "edit" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                    "own" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                ]
            )
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Category::class, $this->sut);
    }

    public function test_it_is_constructed_from_database_data(): void
    {
        $category = Category::fromDatabase([
            'id' => 1,
            'code' => 'my_category',
            'template_uuid' => '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'translations' => '{"fr_FR": "category_libelle"}',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 1,
            'lvl' => 2,
            'parent_id' => 1,
            'updated' => '2021-03-24 16:00:00',
            'value_collection' => '{}',
            "permissions" => '{
                        "view":{"1": "IT Support", "3": "Redactor", "7": "Manager"},
                        "edit":{"1": "IT Support", "3": "Redactor", "7": "Manager"},
                        "own":{"1": "IT Support", "3": "Redactor", "7": "Manager"}
                    }',
        ]);
        $this->assertSame(1, $category->getId()->getValue());
        $this->assertSame('my_category', $category->getCode()->__toString());
        $this->assertSame('02274dac-e99a-4e1d-8f9b-794d4c3ba330', $category->getTemplateUuid()->__toString());
        $this->assertSame(["fr_FR" => "category_libelle"], $category->getLabels()->normalize());
        $this->assertNull($category->getRootId());
        $this->assertSame(1, $category->getParentId()->getValue());
        $this->assertSame('2021-03-24 16:00:00', $category->getUpdated()->format('Y-m-d H:i:s'));
        $this->assertSame([], $category->getAttributes()->normalize());
        $this->assertSame([
            "view" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            "edit" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            "own" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
        ], $category->getPermissions()->normalize());
    }

    public function test_it_is_constructed_from_category_with_permissions(): void
    {
        $category = Category::fromDatabase([
            'id' => 1,
            'code' => 'my_category',
            'template_uuid' => '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'translations' => '{"fr_FR": "category_libelle"}',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 1,
            'lvl' => 2,
            'parent_id' => 1,
            'updated' => '2021-03-24 16:00:00',
            'value_collection' => '{}',
            "permissions" => null,
        ]);
        $this->assertNull($category->getPermissions()->normalize());
        $category = Category::fromCategoryWithPermissions(
            $category,
            [
                "view" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                "edit" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
                "own" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            ]
        );
        $this->assertSame(1, $category->getId()->getValue());
        $this->assertSame('my_category', $category->getCode()->__toString());
        $this->assertSame('02274dac-e99a-4e1d-8f9b-794d4c3ba330', $category->getTemplateUuid()->__toString());
        $this->assertSame(["fr_FR" => "category_libelle"], $category->getLabels()->normalize());
        $this->assertNull($category->getRootId());
        $this->assertSame(1, $category->getParentId()->getValue());
        $this->assertSame('2021-03-24 16:00:00', $category->getUpdated()->format('Y-m-d H:i:s'));
        $this->assertSame([], $category->getAttributes()->normalize());
        $this->assertSame([
            "view" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            "edit" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
            "own" => [1 => "IT Support", 3 => "Redactor", 7 => "Manager"],
        ], $category->getPermissions()->normalize());
    }

    public function test_it_is_set_with_null_label(): void
    {
        $this->sut->setLabel('en_US', null);
        $this->assertNull($this->sut->getLabels()->getTranslation('en_US'));
    }

    public function test_from_database_with_empty_translations(): void
    {
        $category = Category::fromDatabase([
            'id' => 1,
            'code' => 'test',
            'template_uuid' => null,
            'translations' => '',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 2,
            'lvl' => 0,
            'parent_id' => null,
            'updated' => null,
            'value_collection' => null,
            'permissions' => null,
        ]);
        $this->assertSame([], $category->getLabels()->normalize());
    }

    public function test_from_database_with_null_permissions(): void
    {
        $category = Category::fromDatabase([
            'id' => 1,
            'code' => 'test',
            'template_uuid' => null,
            'translations' => '{"en_US": "Test"}',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 2,
            'lvl' => 0,
            'parent_id' => null,
            'updated' => null,
            'value_collection' => null,
            'permissions' => null,
        ]);
        $this->assertNull($category->getPermissions()->normalize());
    }

    public function test_from_database_with_empty_string_permissions(): void
    {
        $category = Category::fromDatabase([
            'id' => 1,
            'code' => 'test',
            'template_uuid' => null,
            'translations' => '{}',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 2,
            'lvl' => 0,
            'parent_id' => null,
            'updated' => null,
            'value_collection' => null,
            'permissions' => '',
        ]);
        // Empty string permissions should be treated as null
        $this->assertNull($category->getPermissions()->normalize());
    }

    public function test_from_database_without_permissions_key(): void
    {
        // If permissions key is not set at all
        $category = Category::fromDatabase([
            'id' => 1,
            'code' => 'test',
            'template_uuid' => null,
            'translations' => '{}',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 2,
            'lvl' => 0,
            'parent_id' => null,
            'updated' => null,
            'value_collection' => null,
        ]);
        $this->assertNull($category->getPermissions()->normalize());
    }

    public function test_from_database_with_value_collection(): void
    {
        $category = Category::fromDatabase([
            'id' => 1,
            'code' => 'test',
            'template_uuid' => null,
            'translations' => '{}',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 2,
            'lvl' => 0,
            'parent_id' => null,
            'updated' => null,
            'value_collection' => '{}',
            'permissions' => null,
        ]);
        $this->assertNotNull($category->getAttributes());
        $this->assertSame([], $category->getAttributes()->normalize());
    }

    public function test_from_database_with_null_value_collection(): void
    {
        $category = Category::fromDatabase([
            'id' => 1,
            'code' => 'test',
            'template_uuid' => null,
            'translations' => '{}',
            'root_id' => null,
            'lft' => 1,
            'rgt' => 2,
            'lvl' => 0,
            'parent_id' => null,
            'updated' => null,
            'value_collection' => null,
            'permissions' => null,
        ]);
        $this->assertNull($category->getAttributes());
    }

    public function test_from_database_position_values(): void
    {
        $category = Category::fromDatabase([
            'id' => 1,
            'code' => 'test',
            'template_uuid' => null,
            'translations' => '{}',
            'root_id' => null,
            'lft' => 5,
            'rgt' => 10,
            'lvl' => 3,
            'parent_id' => null,
            'updated' => null,
            'value_collection' => null,
            'permissions' => null,
        ]);
        $this->assertSame(5, $category->getPosition()->left);
        $this->assertSame(10, $category->getPosition()->right);
        $this->assertSame(3, $category->getPosition()->level);
    }
}

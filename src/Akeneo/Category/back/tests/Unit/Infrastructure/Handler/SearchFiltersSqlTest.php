<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Handler;

use Akeneo\Category\Application\Query\ExternalApiSqlParameters;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\Position;
use Akeneo\Category\Infrastructure\Handler\SearchFiltersSql;
use Akeneo\Category\Infrastructure\Validation\ExternalApiSearchFiltersValidator;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchFiltersSqlTest extends TestCase
{
    private ExternalApiSearchFiltersValidator|MockObject $searchFiltersValidator;
    private GetCategoryInterface|MockObject $getCategory;
    private SearchFiltersSql $sut;

    protected function setUp(): void
    {
        $this->searchFiltersValidator = $this->createMock(ExternalApiSearchFiltersValidator::class);
        $this->getCategory = $this->createMock(GetCategoryInterface::class);
        $this->sut = new SearchFiltersSql(
            $this->searchFiltersValidator,
            $this->getCategory,
        );
    }

    public function test_it_generates_correct_sqlWhere_for_parent_filter(): void
    {
        $value = '3';
        $searchFilters = [
            'parent' => [
                [
                    'operator' => '=',
                    'value' => $value,
                ],
            ],
        ];
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('test'),
            templateUuid: null,
            rootId: new CategoryId(4),
            position: new Position(1, 3, 0),
        );
        $this->searchFiltersValidator->expects($this->once())->method('validate')->with($this->anything());
        $this->getCategory->method('byCode')->with($this->anything())->willReturn($category);
        $params = [
            'left' => $category->getPosition()->left,
            'right' => $category->getPosition()->right,
            'root' => $category->getRootId()->getValue(),
        ];
        $types = [
            'left' => ParameterType::INTEGER,
            'right' => ParameterType::INTEGER,
            'root' => ParameterType::INTEGER,
        ];
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.lft > :left AND category.rgt < :right AND category.root = :root',
            params: $params,
            types: $types,
            limitAndOffset: null,
        );
        $this->assertEquals($expected, $this->sut->build($searchFilters));
    }

    public function test_it_generates_correct_sqlWhere_for_root_filter_set_to_true(): void
    {
        $value =  true;
        $searchFilters = [
            'is_root' => [
                [
                    'operator' => '=',
                    'value' => $value,
                ],
            ],
        ];
        $this->searchFiltersValidator->expects($this->once())->method('validate')->with($this->anything());
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.parent_id IS NULL',
            params: [],
            types: [],
            limitAndOffset: null,
        );
        $this->assertEquals($expected, $this->sut->build($searchFilters));
    }

    public function test_it_generates_correct_sqlWhere_for_root_filter_set_to_false(): void
    {
        $value =  false;
        $searchFilters = [
            'is_root' => [
                [
                    'operator' => '=',
                    'value' => $value,
                ],
            ],
        ];
        $this->searchFiltersValidator->expects($this->once())->method('validate')->with($this->anything());
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.parent_id IS NOT NULL',
            params: [],
            types: [],
            limitAndOffset: null,
        );
        $this->assertEquals($expected, $this->sut->build($searchFilters));
    }

    public function test_it_generates_correct_sqlWhere_for_parent_and_is_root_filters(): void
    {
        $parentValue = '3';
        $isRootValue =  true;
        $searchFilters = [
            'parent' => [
                [
                    'operator' => '=',
                    'value' => $parentValue,
                ],
            ],
            'is_root' => [
                [
                    'operator' => '=',
                    'value' => $isRootValue,
                ],
            ],
        ];
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('test'),
            templateUuid: null,
            rootId: new CategoryId(4),
            position: new Position(1, 3, 0),
        );
        $this->searchFiltersValidator->expects($this->once())->method('validate')->with($this->anything());
        $this->getCategory->method('byCode')->with($this->anything())->willReturn($category);
        $params = [
            'left' => $category->getPosition()->left,
            'right' => $category->getPosition()->right,
            'root' => $category->getRootId()->getValue(),
        ];
        $types = [
            'left' => ParameterType::INTEGER,
            'right' => ParameterType::INTEGER,
            'root' => ParameterType::INTEGER,
        ];
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.lft > :left AND category.rgt < :right AND category.root = :root AND category.parent_id IS NULL',
            params: $params,
            types: $types,
            limitAndOffset: null,
        );
        $this->assertEquals($expected, $this->sut->build($searchFilters));
    }

    public function test_it_generates_correct_sqlWhere_for_category_codes_filter(): void
    {
        $values = ['master', 'category1'];
        $searchFilters = [
            'code' => [
                [
                    'operator' => 'IN',
                    'value' => $values,
                ],
            ],
        ];
        $this->searchFiltersValidator->expects($this->once())->method('validate')->with($this->anything());
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:code_0)',
            params: [
                'code_0' => [
                    'master',
                    'category1',
                ],
            ],
            types: [
                'code_0' => ArrayParameterType::STRING,
            ],
            limitAndOffset: null,
        );
        $this->assertEquals($expected, $this->sut->build($searchFilters));
    }

    public function test_it_generates_correct_sqlWhere_for_greater_than_date_filter(): void
    {
        $value = '2019-06-09T12:00:00+00:00';
        $searchFilters = [
            'updated' => [
                [
                    'operator' => '>',
                    'value' => $value,
                ],
            ],
        ];
        $this->searchFiltersValidator->expects($this->once())->method('validate')->with($this->anything());
        $params = [
            'updated_0' => $value,
        ];
        $types = [
            'updated_0' => ParameterType::STRING,
        ];
        $expected = new ExternalApiSqlParameters(
            sqlWhere: 'category.updated > :updated_0',
            params: $params,
            types: $types,
            limitAndOffset: null,
        );
        $this->assertEquals($expected, $this->sut->build($searchFilters));
    }

    public function test_it_throws_exception_on_bad_operator(): void
    {
        $value = '2019-06-09T12:00:00+00:00';
        $searchFilters = [
            'updated' => [
                [
                    'operator' => '!=',
                    'value' => $value,
                ],
            ],
        ];
        $this->searchFiltersValidator->expects($this->once())->method('validate')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->build($searchFilters);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\ExternalApiSqlParameters;
use Akeneo\Category\Application\Query\GetCategoriesParametersBuilder;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetCategoriesParametersBuilderSqlIntegration extends CategoryTestCase
{
    public function testBuildParameters(): void
    {
        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => ["sock"],
                ],
            ],
        ];

        $parameters = $this->get(GetCategoriesParametersBuilder::class)->build(
            searchFilters: $searchFilters,
            limit: 10,
            offset: 3,
            withPosition: false,
            isEnrichedAttributes: true
        );

        $expectedParameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:code_0)',
            params: [
                'code_0' => ['sock'],
                'limit' => 10,
                'offset' => 3,
                'with_position' => false,
                'with_enriched_attributes' => true,
            ],
            types: [
                'code_0' => ArrayParameterType::STRING,
                'limit' => ParameterType::INTEGER,
                'offset' => ParameterType::INTEGER,
                'with_position' => ParameterType::BOOLEAN,
                'with_enriched_attributes' => ParameterType::BOOLEAN,
            ],
            limitAndOffset: 'LIMIT :limit OFFSET :offset',
        );

        $this->assertEqualsCanonicalizing($expectedParameters, $parameters);
    }

    public function testBuildParametersWithNoOffset(): void
    {
        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => ["sock"],
                ],
            ],
        ];

        $parameters = $this->get(GetCategoriesParametersBuilder::class)->build(
            searchFilters: $searchFilters,
            limit: 10,
            offset: 0,
            withPosition: false,
            isEnrichedAttributes: true
        );

        $expectedParameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:code_0)',
            params: [
                'code_0' => ['sock'],
                'limit' => 10,
                'with_position' => false,
                'with_enriched_attributes' => true,
            ],
            types: [
                'code_0' => ArrayParameterType::STRING,
                'limit' => ParameterType::INTEGER,
                'with_position' => ParameterType::BOOLEAN,
                'with_enriched_attributes' => ParameterType::BOOLEAN,
            ],
            limitAndOffset: 'LIMIT :limit',
        );

        $this->assertEqualsCanonicalizing($expectedParameters, $parameters);
    }

    public function testBuildParametersWithNoCategoryCodes(): void
    {
        $parameters = $this->get(GetCategoriesParametersBuilder::class)->build(
            searchFilters: [],
            limit: 10,
            offset: 3,
            withPosition: false,
            isEnrichedAttributes: true
        );

        $expectedParameters = new ExternalApiSqlParameters(
            sqlWhere: '1=1',
            params: [
                'limit' => 10,
                'offset' => 3,
                'with_position' => false,
                'with_enriched_attributes' => true,
            ],
            types: [
                'limit' => ParameterType::INTEGER,
                'offset' => ParameterType::INTEGER,
                'with_position' => ParameterType::BOOLEAN,
                'with_enriched_attributes' => ParameterType::BOOLEAN,
            ],
            limitAndOffset: 'LIMIT :limit OFFSET :offset',
        );

        $this->assertEqualsCanonicalizing($expectedParameters, $parameters);
    }

    public function testBuildParametersWithNoEnrichedAttributes(): void
    {
        $searchFilters = [
            "code" => [
                [
                    "operator" => "IN",
                    "value" => ["sock"],
                ],
            ],
        ];

        $parameters = $this->get(GetCategoriesParametersBuilder::class)->build(
            searchFilters: $searchFilters,
            limit: 10,
            offset: 3,
            withPosition: false,
            isEnrichedAttributes: false
        );

        $expectedParameters = new ExternalApiSqlParameters(
            sqlWhere: 'category.code IN (:code_0)',
            params: [
                'code_0' => ['sock'],
                'limit' => 10,
                'offset' => 3,
                'with_position' => false,
                'with_enriched_attributes' => false,
            ],
            types: [
                'code_0' => ArrayParameterType::STRING,
                'limit' => ParameterType::INTEGER,
                'offset' => ParameterType::INTEGER,
                'with_position' => ParameterType::BOOLEAN,
                'with_enriched_attributes' => ParameterType::BOOLEAN,
            ],
            limitAndOffset: 'LIMIT :limit OFFSET :offset',
        );

        $this->assertEqualsCanonicalizing($expectedParameters, $parameters);
    }

    public function testBuildParametersWithPosition(): void
    {
        $searchFilters = [];

        $parameters = $this->get(GetCategoriesParametersBuilder::class)->build(
            searchFilters: $searchFilters,
            limit: 10,
            offset: 3,
            withPosition: true,
            isEnrichedAttributes: false
        );

        $expectedParameters = new ExternalApiSqlParameters(
            sqlWhere: '1=1',
            params: [
                'limit' => 10,
                'offset' => 3,
                'with_position' => true,
                'with_enriched_attributes' => false,
            ],
            types: [
                'limit' => ParameterType::INTEGER,
                'offset' => ParameterType::INTEGER,
                'with_position' => ParameterType::BOOLEAN,
                'with_enriched_attributes' => ParameterType::BOOLEAN,
            ],
            limitAndOffset: 'LIMIT :limit OFFSET :offset',
        );

        $this->assertEqualsCanonicalizing($expectedParameters, $parameters);
    }
}

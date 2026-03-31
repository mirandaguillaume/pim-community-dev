<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\SearchAfterHalPaginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SearchAfterHalPaginatorTest extends TestCase
{
    private RouterInterface|MockObject $router;
    private SearchAfterHalPaginator $sut;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->sut = new SearchAfterHalPaginator($this->router);
    }

    private function setupRouterGenerateMap(array $map): void
    {
        $this->router->method('generate')->willReturnCallback(
            function (string $route, array $params, int $refType) use ($map): string {
                foreach ($map as [$mapRoute, $mapParams, $mapRefType, $mapResult]) {
                    if ($route === $mapRoute && $refType === $mapRefType) {
                        // Compare params ignoring key order
                        ksort($params);
                        ksort($mapParams);
                        if ($params == $mapParams) {
                            return $mapResult;
                        }
                    }
                }
                throw new \RuntimeException(sprintf(
                    'Unexpected generate call: route=%s, params=%s',
                    $route,
                    json_encode($params)
                ));
            }
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SearchAfterHalPaginator::class, $this->sut);
    }

    public function test_it_is_a_paginator(): void
    {
        $this->assertInstanceOf(PaginatorInterface::class, $this->sut);
    }

    public function test_it_paginates_in_hal_format(): void
    {
        $this->setupRouterGenerateMap([
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text', 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit' => 2, 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'another_text', 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=another_text'],
            ['attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA'],
            ['attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB'],
        ]);
        $standardItems = [['code' => 'optionA'], ['code' => 'optionB']];
        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
            'search_after'        => ['self' => 'a_text', 'next' => 'another_text'],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];
        $result = $this->sut->paginate($standardItems, $parameters, null);
        $this->assertSame('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text', $result['_links']['self']['href']);
        $this->assertSame('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2', $result['_links']['first']['href']);
        $this->assertSame('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=another_text', $result['_links']['next']['href']);
        $this->assertCount(2, $result['_embedded']['items']);
    }

    public function test_it_paginates_in_hal_format_without_using_the_limit_as_query_parameter(): void
    {
        $this->setupRouterGenerateMap([
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=a_text'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'another_text'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=another_text'],
            ['attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA'],
            ['attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB'],
        ]);
        $standardItems = [['code' => 'optionA'], ['code' => 'optionB']];
        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['pagination_type' => 'search_after'],
            'search_after'        => ['self' => 'a_text', 'next' => 'another_text'],
            'limit'               => 2,
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];
        $result = $this->sut->paginate($standardItems, $parameters, null);
        $this->assertSame('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=a_text', $result['_links']['self']['href']);
        $this->assertSame('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after', $result['_links']['first']['href']);
        $this->assertSame('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=another_text', $result['_links']['next']['href']);
        $this->assertCount(2, $result['_embedded']['items']);
    }

    public function test_it_paginates_without_next_link_when_last_page(): void
    {
        $this->setupRouterGenerateMap([
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text', 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit' => 2, 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2'],
            ['attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA'],
        ]);
        $standardItems = [['code' => 'optionA']];
        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
            'search_after'        => ['self' => 'a_text', 'next' => 'another_text'],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];
        $result = $this->sut->paginate($standardItems, $parameters, null);
        $this->assertArrayNotHasKey('next', $result['_links']);
        $this->assertCount(1, $result['_embedded']['items']);
    }

    public function test_it_paginates_with_one_page_when_total_items_equals_zero(): void
    {
        $this->setupRouterGenerateMap([
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit' => 2, 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2'],
        ]);
        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
            'search_after'        => ['self' => null, 'next' => null],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];
        $result = $this->sut->paginate([], $parameters, null);
        $this->assertSame('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2', $result['_links']['self']['href']);
        $this->assertSame('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2', $result['_links']['first']['href']);
        $this->assertEmpty($result['_embedded']['items']);
    }

    public function test_it_throws_an_exception_when_unknown_parameter_given(): void
    {
        $this->expectException(PaginationParametersException::class);
        $this->sut->paginate([], ['foo' => 'bar'], null);
    }

    public function test_it_throws_an_exception_when_a_parameter_is_missing(): void
    {
        $this->expectException(PaginationParametersException::class);
        $this->sut->paginate([], [], null);
    }

    public function test_it_throws_an_exception_when_no_limit_has_been_defined(): void
    {
        $parameters = [
            'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
            'query_parameters'    => ['pagination_type' => 'search_after'],
            'search_after'        => ['self' => null, 'next' => null],
            'list_route_name'     => 'attribute_option_list_route',
            'item_route_name'     => 'attribute_option_item_route',
            'item_identifier_key' => 'code',
        ];
        $this->expectException(PaginationParametersException::class);
        $this->sut->paginate([], $parameters, null);
    }
}

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
        // links
        $this->router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text', 'limit' => 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text');
        $this->router->method('generate')->with(
            'attribute_option_list_route',
            ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit' => 2, 'search_after' => null],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');
        $this->router->method('generate')->with(
            'attribute_option_list_route',
            ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'another_text', 'limit' => 2],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=another_text');
        // embedded
        $this->router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA');
        $this->router->method('generate')->with('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB');
        $standardItems = [
                    ['code'   => 'optionA'],
                    ['code'   => 'optionB'],
                ];
        $expectedItems = [
                    '_links'       => [
                        'self'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text',
                        ],
                        'first'    => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                        ],
                        'next'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=another_text',
                        ],
                    ],
                    '_embedded'    => [
                        'items' => [
                            [
                                '_links' => [
                                    'self' => [
                                        'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA',
                                    ],
                                ],
                                'code'   => 'optionA',
                            ],
                            [
                                '_links' => [
                                    'self' => [
                                        'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB',
                                    ],
                                ],
                                'code'   => 'optionB',
                            ],
                        ],
                    ],
                ];
        $parameters = [
                    'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
                    'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
                    'search_after'        => ['self' => 'a_text', 'next' => 'another_text'],
                    'list_route_name'     => 'attribute_option_list_route',
                    'item_route_name'     => 'attribute_option_item_route',
                    'item_identifier_key' => 'code',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate($standardItems, $parameters, null));
    }

    public function test_it_paginates_in_hal_format_without_using_the_limit_as_query_parameter(): void
    {
        // links
        $this->router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text'],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=a_text');
        $this->router->method('generate')->with(
            'attribute_option_list_route',
            ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => null],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after');
        $this->router->method('generate')->with(
            'attribute_option_list_route',
            ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'another_text'],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=another_text');
        // embedded
        $this->router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA');
        $this->router->method('generate')->with('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB');
        $standardItems = [
                    ['code'   => 'optionA'],
                    ['code'   => 'optionB'],
                ];
        $expectedItems = [
                    '_links'       => [
                        'self'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=a_text',
                        ],
                        'first'    => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after',
                        ],
                        'next'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&search_after=another_text',
                        ],
                    ],
                    '_embedded'    => [
                        'items' => [
                            [
                                '_links' => [
                                    'self' => [
                                        'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA',
                                    ],
                                ],
                                'code'   => 'optionA',
                            ],
                            [
                                '_links' => [
                                    'self' => [
                                        'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB',
                                    ],
                                ],
                                'code'   => 'optionB',
                            ],
                        ],
                    ],
                ];
        $parameters = [
                    'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
                    'query_parameters'    => ['pagination_type' => 'search_after'],
                    'search_after'        => ['self' => 'a_text', 'next' => 'another_text'],
                    'limit'               => 2,
                    'list_route_name'     => 'attribute_option_list_route',
                    'item_route_name'     => 'attribute_option_item_route',
                    'item_identifier_key' => 'code',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate($standardItems, $parameters, null));
    }

    public function test_it_paginates_without_next_link_when_last_page(): void
    {
        // links
        $this->router
            ->generate(
                'attribute_option_list_route',
                ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'search_after' => 'a_text', 'limit' => 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text');
        $this->router->method('generate')->with(
            'attribute_option_list_route',
            ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit' => 2, 'search_after' => null],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');
        // embedded
        $this->router
            ->generate('attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA', 'search_after' => null], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA');
        $standardItems = [
                    ['code'   => 'optionA'],
                ];
        $expectedItems = [
                    '_links'       => [
                        'self'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2&search_after=a_text',
                        ],
                        'first'    => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                        ],
                    ],
                    '_embedded'    => [
                        'items' => [
                            [
                                '_links' => [
                                    'self' => [
                                        'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA',
                                    ],
                                ],
                                'code'   => 'optionA',
                            ],
                        ],
                    ],
                ];
        $parameters = [
                    'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
                    'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
                    'search_after'        => ['self' => 'a_text', 'next' => 'another_text'],
                    'list_route_name'     => 'attribute_option_list_route',
                    'item_route_name'     => 'attribute_option_item_route',
                    'item_identifier_key' => 'code',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate($standardItems, $parameters, null));
    }

    public function test_it_paginates_with_one_page_when_total_items_equals_zero(): void
    {
        $this->router->method('generate')->with(
            'attribute_option_list_route',
            ['attributeCode' => 'a_multi_select', 'pagination_type' => 'search_after', 'limit' => 2, 'search_after' => null],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2');
        $expectedItems = [
                    '_links'       => [
                        'self'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                        ],
                        'first'    => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?pagination_type=search_after&limit=2',
                        ],
                    ],
                    '_embedded'    => [
                        'items' => [],
                    ],
                ];
        $parameters = [
                    'uri_parameters'      => ['attributeCode' => 'a_multi_select'],
                    'query_parameters'    => ['limit' => 2, 'pagination_type' => 'search_after'],
                    'search_after'        => ['self' => null, 'next' => null],
                    'list_route_name'     => 'attribute_option_list_route',
                    'item_route_name'     => 'attribute_option_item_route',
                    'item_identifier_key' => 'code',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate([], $parameters, null));
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

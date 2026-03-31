<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Pagination;

use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\OffsetHalPaginator;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class OffsetHalPaginatorTest extends TestCase
{
    private RouterInterface|MockObject $router;
    private OffsetHalPaginator $sut;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->sut = new OffsetHalPaginator($this->router);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(OffsetHalPaginator::class, $this->sut);
    }

    public function test_it_is_a_paginator(): void
    {
        $this->assertInstanceOf(PaginatorInterface::class, $this->sut);
    }

    public function test_it_paginates_in_hal_format_without_count(): void
    {
        $this->router->method('generate')->willReturnMap([
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 3, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=3'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 1, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=1'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 2, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=2'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 4, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=4'],
            ['attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA'],
            ['attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB'],
        ]);
        $standardItems = [
                    ['code'   => 'optionA'],
                    ['code'   => 'optionB'],
                ];
        $expectedItems = [
                    '_links'       => [
                        'self'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=3',
                        ],
                        'first'    => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=1',
                        ],
                        'previous' => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=2',
                        ],
                        'next'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=4',
                        ],
                    ],
                    'current_page' => 3,
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
                    'query_parameters'    => ['page' => 3, 'limit' => 2],
                    'list_route_name'     => 'attribute_option_list_route',
                    'item_route_name'     => 'attribute_option_item_route',
                    'item_identifier_key' => 'code',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate($standardItems, $parameters, null));
    }

    public function test_it_paginates_in_hal_format_with_count(): void
    {
        $this->router->method('generate')->willReturnMap([
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 3, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=3'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 1, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=1'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 2, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=2'],
            ['attribute_option_list_route', ['attributeCode' => 'a_multi_select', 'page' => 4, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=4'],
            ['attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionA'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionA'],
            ['attribute_option_item_route', ['attributeCode' => 'a_multi_select', 'code' => 'optionB'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options/optionB'],
        ]);
        $standardItems = [
                    ['code'   => 'optionA'],
                    ['code'   => 'optionB'],
                ];
        $expectedItems = [
                    '_links'       => [
                        'self'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=3',
                        ],
                        'first'    => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=1',
                        ],
                        'previous' => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=2',
                        ],
                        'next'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/attributes/a_multi_select/options?limit=2&page=4',
                        ],
                    ],
                    'current_page' => 3,
                    'items_count'  => 990,
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
                    'query_parameters'    => ['page' => 3, 'limit' => 2],
                    'list_route_name'     => 'attribute_option_list_route',
                    'item_route_name'     => 'attribute_option_item_route',
                    'item_identifier_key' => 'code',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate($standardItems, $parameters, 990));
    }

    public function test_it_paginates_without_previous_link_when_first_page(): void
    {
        $this->router->method('generate')->willReturnMap([
            ['category_list_route', ['page' => 2, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/?limit=2&page=2'],
            ['category_list_route', ['page' => 1, 'limit' => 2], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/?limit=2&page=1'],
            ['category_item_route', ['code' => 'master'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/master'],
            ['category_item_route', ['code' => 'sales'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/sales'],
        ]);
        $standardItems = [
                    [
                        'code'   => 'master',
                        'parent' => null,
                    ],
                    [
                        'code'   => 'sales',
                        'parent' => 'master',
                    ],
                ];
        $expectedItems = [
                    '_links'       => [
                        'self'  => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=2&page=1',
                        ],
                        'first' => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=2&page=1',
                        ],
                        'next'  => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=2&page=2',
                        ],
                    ],
                    'current_page' => 1,
                    'items_count'  => 990,
                    '_embedded'    => [
                        'items' => [
                            [
                                '_links' => [
                                    'self' => [
                                        'href' => 'http://akeneo.com/api/rest/v1/categories/master',
                                    ],
                                ],
                                'code'   => 'master',
                                'parent' => null,
                            ],
                            [
                                '_links' => [
                                    'self' => [
                                        'href' => 'http://akeneo.com/api/rest/v1/categories/sales',
                                    ],
                                ],
                                'code'   => 'sales',
                                'parent' => 'master',
                            ],
                        ],
                    ],
                ];
        $parameters = [
                    'query_parameters'    => ['page' => 1, 'limit' => 2],
                    'list_route_name'     => 'category_list_route',
                    'item_route_name'     => 'category_item_route',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate($standardItems, $parameters, 990));
    }

    public function test_it_paginates_without_next_link_when_last_page(): void
    {
        $this->router->method('generate')->willReturnMap([
            ['category_list_route', ['page' => 1, 'limit' => 100], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1'],
            ['category_list_route', ['page' => 10, 'limit' => 100], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10'],
            ['category_list_route', ['page' => 9, 'limit' => 100], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=9'],
            ['category_item_route', ['code' => 'master'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/master'],
            ['category_item_route', ['code' => 'sales'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/sales'],
        ]);
        $standardItems = [
                    [
                        'code'   => 'master',
                        'parent' => null,
                    ],
                    [
                        'code'   => 'sales',
                        'parent' => 'master',
                    ],
                ];
        $expectedItems = [
                    '_links'       => [
                        'self'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10',
                        ],
                        'first'    => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                        ],
                        'previous' => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=9',
                        ],
                    ],
                    'current_page' => 10,
                    'items_count'  => 990,
                    '_embedded'    => [
                        'items' => [
                            [
                                '_links' => [
                                    'self' => [
                                        'href' => 'http://akeneo.com/api/rest/v1/categories/master',
                                    ],
                                ],
                                'code'   => 'master',
                                'parent' => null,
                            ],
                            [
                                '_links' => [
                                    'self' => [
                                        'href' => 'http://akeneo.com/api/rest/v1/categories/sales',
                                    ],
                                ],
                                'code'   => 'sales',
                                'parent' => 'master',
                            ],
                        ],
                    ],
                ];
        $parameters = [
                    'query_parameters'    => ['page' => 10, 'limit' => 100],
                    'list_route_name'     => 'category_list_route',
                    'item_route_name'     => 'category_item_route',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate($standardItems, $parameters, 990));
    }

    public function test_it_paginates_with_previous_and_without_next_link_when_nonexistent_page(): void
    {
        $this->router->method('generate')->willReturnMap([
            ['category_list_route', ['page' => 11, 'limit' => 100], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=11'],
            ['category_list_route', ['page' => 1, 'limit' => 100], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1'],
            ['category_list_route', ['page' => 10, 'limit' => 100], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10'],
        ]);
        $expectedItems = [
                    '_links'       => [
                        'self'     => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=11',
                        ],
                        'first'    => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                        ],
                        'previous' => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=10',
                        ],
                    ],
                    'current_page' => 11,
                    '_embedded'    => [
                        'items' => [],
                    ],
                ];
        $parameters = [
                    'query_parameters'    => ['page' => 11, 'limit' => 100],
                    'list_route_name'     => 'category_list_route',
                    'item_route_name'     => 'category_item_route',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate([], $parameters, null));
    }

    public function test_it_paginates_with_one_page_when_total_items_equals_zero(): void
    {
        $this->router->method('generate')->willReturnMap([
            ['category_list_route', ['page' => 1, 'limit' => 100], UrlGeneratorInterface::ABSOLUTE_URL, 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1'],
        ]);
        $expectedItems = [
                    '_links'       => [
                        'self'  => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                        ],
                        'first' => [
                            'href' => 'http://akeneo.com/api/rest/v1/categories/?limit=100&page=1',
                        ],
                    ],
                    'current_page' => 1,
                    'items_count'  => 0,
                    '_embedded'    => [
                        'items' => [],
                    ],
                ];
        $parameters = [
                    'query_parameters'    => ['page' => 1, 'limit' => 100],
                    'list_route_name'     => 'category_list_route',
                    'item_route_name'     => 'category_item_route',
                ];
        $this->assertSame($expectedItems, $this->sut->paginate([], $parameters, 0));
    }

    public function test_it_throws_an_exception_when_unknown_parameter_given(): void
    {
        $this->expectException(PaginationParametersException::class);
        $this->sut->paginate([], ['foo' => 'bar'], 0);
    }

    public function test_it_throws_an_exception_when_a_parameter_is_missing(): void
    {
        $this->expectException(PaginationParametersException::class);
        $this->sut->paginate([], [], 0);
    }
}

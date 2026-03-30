<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Hal;

use Akeneo\Tool\Component\Api\Hal\HalResource;
use Akeneo\Tool\Component\Api\Hal\Link;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HalResourceTest extends TestCase
{
    private HalResource $sut;

    protected function setUp(): void
    {
        $this->sut = new HalResource([], [], []);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(HalResource::class, $this->sut);
    }

    public function test_it_generates_an_hal_array_with_links_and_data_and_embedded_resources(): void
    {
        $resource = $this->createMock(HalResource::class);
        $self = $this->createMock(Link::class);
        $href = $this->createMock(Link::class);

        $self->method('toArray')->willReturn([
                        'self' => [
                            'href' => 'http://akeneo.com/self',
                        ],
                    ]);
        $href->method('toArray')->willReturn([
                        'next' => [
                            'href' => 'http://akeneo.com/next',
                        ],
                    ]);
        $resource->method('toArray')->willReturn([
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/resource/id',
                            ],
                        ],
                        'data'   => 'item_data',
                    ]);
        $this->sut = new HalResource([$self, $href], ['items' => [$resource]], ['total_items' => 1]);
        $this->assertSame([
                        '_links'      => [
                            'self' => [
                                'href' => 'http://akeneo.com/self',
                            ],
                            'next' => [
                                'href' => 'http://akeneo.com/next',
                            ],
                        ],
                        'total_items' => 1,
                        '_embedded'   => [
                            'items' => [
                                [
                                    '_links' => [
                                        'self' => [
                                            'href' => 'http://akeneo.com/api/resource/id',
                                        ],
                                    ],
                                    'data'   => 'item_data',
                                ],
                            ],
                        ],
                    ], $this->sut->toArray());
    }

    public function test_it_generates_an_hal_array_without_any_embedded_resources(): void
    {
        $link = $this->createMock(Link::class);

        $link->method('toArray')->willReturn([
                        'next' => [
                            'href' => 'http://akeneo.com/next',
                        ],
                    ]);
        $this->sut = new HalResource([$link], [], ['total_items' => 1]);
        $this->assertSame([
                        '_links'      => [
                            'next' => [
                                'href' => 'http://akeneo.com/next',
                            ],
                        ],
                        'total_items' => 1,
                    ], $this->sut->toArray());
    }

    public function test_it_generates_an_array_without_link_or_embedded_resources(): void
    {
        $this->sut = new HalResource([], [], []);
        $this->assertSame([
                    ], $this->sut->toArray());
    }

    public function test_it_generates_an_hal_array_with_an_empty_list_of_embedded_resources(): void
    {
        $this->sut = new HalResource([], ['items' => []], []);
        $this->assertSame([
                        '_embedded'   => [
                            'items' => [],
                        ],
                    ], $this->sut->toArray());
    }

    public function test_it_generates_an_hal_array_with_links_in_embedded_resources(): void
    {
        $resource = $this->createMock(HalResource::class);
        $self = $this->createMock(Link::class);
        $href = $this->createMock(Link::class);

        $self->method('toArray')->willReturn([
                        'self' => [
                            'href' => 'http://akeneo.com/self',
                        ],
                    ]);
        $href->method('toArray')->willReturn([
                        'next' => [
                            'href' => 'http://akeneo.com/next',
                        ],
                    ]);
        $resource->method('toArray')->willReturn([
                        '_links' => [
                            'self' => [
                                'href' => 'http://akeneo.com/api/resource/id',
                            ],
                            'download' => [
                                'href' => 'http://akeneo.com/api/resource/download',
                            ],
                        ],
                        'data'   => 'item_data',
                    ]);
        $this->sut = new HalResource([$self, $href], ['items' => [$resource]], ['total_items' => 1]);
        $this->assertSame([
                        '_links'      => [
                            'self' => [
                                'href' => 'http://akeneo.com/self',
                            ],
                            'next' => [
                                'href' => 'http://akeneo.com/next',
                            ],
                        ],
                        'total_items' => 1,
                        '_embedded'   => [
                            'items' => [
                                [
                                    '_links' => [
                                        'self' => [
                                            'href' => 'http://akeneo.com/api/resource/id',
                                        ],
                                        'download' => [
                                            'href' => 'http://akeneo.com/api/resource/download',
                                        ],
                                    ],
                                    'data'   => 'item_data',
                                ],
                            ],
                        ],
                    ], $this->sut->toArray());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Hal;

use Akeneo\Tool\Component\Api\Hal\Link;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    private Link $sut;

    protected function setUp(): void
    {
        $this->sut = new Link('self', 'http://akeneo.com');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Link::class, $this->sut);
    }

    public function test_it_creates_a_link(): void
    {
        $this->assertSame('self', $this->sut->getRel());
        $this->assertSame('http://akeneo.com', $this->sut->getUrl());
    }

    public function test_it_creates_an_hal_link(): void
    {
        $link = [
                    'self' => [
                        'href' => 'http://akeneo.com',
                    ],
                ];
        $this->assertSame($link, $this->sut->toArray());
    }
}

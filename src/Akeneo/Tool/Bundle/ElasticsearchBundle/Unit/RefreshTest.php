<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PHPUnit\Framework\TestCase;

class RefreshTest extends TestCase
{
    private Refresh $sut;

    protected function setUp(): void
    {
    }

    public function test_it_creates_a_enable_refresh_param(): void
    {
        $this->sut->beConstructedThrough('enable');
        $this->assertSame(Refresh::ENABLE, $this->sut->getType());
    }

    public function test_it_creates_a_disable_refresh_param(): void
    {
        $this->sut->beConstructedThrough('disable');
        $this->assertSame(Refresh::DISABLE, $this->sut->getType());
    }

    public function test_it_creates_a_wait_for_refresh_param(): void
    {
        $this->sut->beConstructedThrough('waitFor');
        $this->assertSame(Refresh::WAIT_FOR, $this->sut->getType());
    }
}

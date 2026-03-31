<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PHPUnit\Framework\TestCase;

class RefreshTest extends TestCase
{
    public function test_it_creates_a_enable_refresh_param(): void
    {
        $sut = Refresh::enable();
        $this->assertSame(Refresh::ENABLE, $sut->getType());
    }

    public function test_it_creates_a_disable_refresh_param(): void
    {
        $sut = Refresh::disable();
        $this->assertSame(Refresh::DISABLE, $sut->getType());
    }

    public function test_it_creates_a_wait_for_refresh_param(): void
    {
        $sut = Refresh::waitFor();
        $this->assertSame(Refresh::WAIT_FOR, $sut->getType());
    }
}

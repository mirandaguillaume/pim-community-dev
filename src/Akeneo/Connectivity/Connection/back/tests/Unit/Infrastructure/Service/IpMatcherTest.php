<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Service\IpMatcher;

class IpMatcherTest extends TestCase
{
    private IpMatcher $sut;

    protected function setUp(): void
    {
        $this->sut = new IpMatcher();
    }

    public function test_it_does_not_match_if_the_whitelist_is_empty(): void
    {
        $this->assertSame(false, $this->sut->match('168.212.226.204', []));
    }

    public function test_it_does_not_match_if_not_in_the_whitelist(): void
    {
        $this->assertSame(false, $this->sut->match('168.212.226.204', ['10.0.0.0']));
    }

    public function test_it_match_if_in_the_whitelist(): void
    {
        $this->assertSame(true, $this->sut->match('168.212.226.204', ['168.212.226.0/24']));
    }
}

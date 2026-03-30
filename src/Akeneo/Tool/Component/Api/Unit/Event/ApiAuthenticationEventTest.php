<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Event;

use Akeneo\Tool\Component\Api\Event\ApiAuthenticationEvent;
use PHPUnit\Framework\TestCase;

class ApiAuthenticationEventTest extends TestCase
{
    private ApiAuthenticationEvent $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_an_event(): void
    {
        $this->sut = new ApiAuthenticationEvent('magento', '42');
        $this->assertTrue(is_a(ApiAuthenticationEvent::class, ApiAuthenticationEvent::class, true));
    }

    public function test_it_provides_username(): void
    {
        $this->sut = new ApiAuthenticationEvent('magento', '42');
        $this->assertSame('magento', $this->sut->username());
    }

    public function test_it_provides_client_id(): void
    {
        $this->sut = new ApiAuthenticationEvent('magento', '42');
        $this->assertSame('42', $this->sut->clientId());
    }
}

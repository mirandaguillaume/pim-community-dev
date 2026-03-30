<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ActiveWebhookTest extends TestCase
{
    private ActiveWebhook $sut;

    protected function setUp(): void
    {
        $this->sut = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook', true);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ActiveWebhook::class, $this->sut);
    }

    public function test_it_returns_a_connection_code(): void
    {
        $this->assertSame('ecommerce', $this->sut->connectionCode());
    }

    public function test_it_returns_a_user_id(): void
    {
        $this->assertSame(0, $this->sut->userId());
    }

    public function test_it_returns_a_secret(): void
    {
        $this->assertSame('a_secret', $this->sut->secret());
    }

    public function test_it_returns_an_url(): void
    {
        $this->assertSame('http://localhost/webhook', $this->sut->url());
    }

    public function test_it_returns_uuid_usage_status(): void
    {
        $this->assertSame(true, $this->sut->isUsingUuid());
    }
}

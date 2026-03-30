<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Query;

use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookQuery;
use PHPUnit\Framework\TestCase;

class GetAConnectionWebhookQueryTest extends TestCase
{
    private GetAConnectionWebhookQuery $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_get_a_connection_webhook_query(): void
    {
        $this->sut = new GetAConnectionWebhookQuery('magento');
        $this->assertTrue(is_a(GetAConnectionWebhookQuery::class, GetAConnectionWebhookQuery::class, true));
    }

    public function test_it_provides_a_code(): void
    {
        $this->sut = new GetAConnectionWebhookQuery('magento');
        $this->assertSame('magento', $this->sut->code());
    }
}

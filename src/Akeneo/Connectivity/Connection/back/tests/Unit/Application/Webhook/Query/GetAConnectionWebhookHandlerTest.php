<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Query;

use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookQuery;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\EventSubscriptionFormData;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\CountActiveEventSubscriptionsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetAConnectionWebhookQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetAConnectionWebhookHandlerTest extends TestCase
{
    private GetAConnectionWebhookQueryInterface|MockObject $getAConnectionWebhookQuery;
    private CountActiveEventSubscriptionsQueryInterface|MockObject $countActiveEventSubscriptionsQuery;
    private GetAConnectionWebhookHandler $sut;

    protected function setUp(): void
    {
        $this->getAConnectionWebhookQuery = $this->createMock(GetAConnectionWebhookQueryInterface::class);
        $this->countActiveEventSubscriptionsQuery = $this->createMock(CountActiveEventSubscriptionsQueryInterface::class);
        $this->sut = new GetAConnectionWebhookHandler(
            $this->getAConnectionWebhookQuery,
            self::ACTIVE_EVENT_SUBSCRIPTIONS_LIMIT,
            $this->countActiveEventSubscriptionsQuery
        );
    }

    public function test_it_is_a_handler(): void
    {
        $this->assertInstanceOf(GetAConnectionWebhookHandler::class, $this->sut);
    }

    public function test_it_gets_a_connection_webhook_given_a_provided_code(): void
    {
        $eventSubscription = new ConnectionWebhook(
            'magento',
            true,
            '1234_secret',
            'any-url.com'
        );
        $this->getAConnectionWebhookQuery->method('execute')->with('magento')->willReturn($eventSubscription);
        $this->countActiveEventSubscriptionsQuery->method('execute')->willReturn(2);
        $expectedFormData = new EventSubscriptionFormData(
            $eventSubscription,
            self::ACTIVE_EVENT_SUBSCRIPTIONS_LIMIT,
            2
        );
        $this->assertEquals($expectedFormData, $this->sut->handle(new GetAConnectionWebhookQuery('magento')));
    }

    public function test_it_returns_null_if_no_connection_webhook_exists(): void
    {
        $this->getAConnectionWebhookQuery->method('execute')->with('magento')->willReturn(null);
        $this->assertNull($this->sut->handle(new GetAConnectionWebhookQuery('magento')));
    }
}

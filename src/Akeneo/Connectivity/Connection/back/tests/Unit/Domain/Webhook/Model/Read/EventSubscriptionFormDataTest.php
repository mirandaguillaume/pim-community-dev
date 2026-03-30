<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\EventSubscriptionFormData;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventSubscriptionFormDataTest extends TestCase
{
    private EventSubscriptionFormData $sut;

    protected function setUp(): void
    {
        $this->sut = new EventSubscriptionFormData(new ConnectionWebhook('erp', true), 3, 2);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventSubscriptionFormData::class, $this->sut);
    }

    public function test_it_normalizes(): void
    {
        $this->assertSame([
                    'event_subscription' => [
                        'connectionCode' => 'erp',
                        'enabled' => true,
                        'secret' => null,
                        'url' => null,
                        'isUsingUuid' => false,
                    ],
                    'active_event_subscriptions_limit' => [
                        'limit' => 3,
                        'current' => 2,
                    ],
                ], $this->sut->normalize());
    }
}

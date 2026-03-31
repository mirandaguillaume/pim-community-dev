<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Webhook\Client;

use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebhookRequestTest extends TestCase
{
    private WebhookRequest $sut;

    protected function setUp(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $this->sut = new WebhookRequest(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook', false),
            [
                new WebhookEvent(
                    'product.created',
                    '79fc4791-86d6-4d3b-93c5-76b787af9497',
                    '2020-01-01T00:00:00+00:00',
                    $author,
                    'staging.akeneo.com',
                    ['identifier' => '1'],
                    $this->createEvent($author, ['identifier' => '1'])
                ),
            ]
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(WebhookRequest::class, $this->sut);
    }

    public function test_it_returns_an_url(): void
    {
        $this->assertSame('http://localhost/webhook', $this->sut->url());
    }

    public function test_it_returns_a_secret(): void
    {
        $this->assertSame('a_secret', $this->sut->secret());
    }

    public function test_it_returns_a_content(): void
    {
        $this->assertSame([
                            'events' => [
                                [
                                    'action' => 'product.created',
                                    'event_id' => '79fc4791-86d6-4d3b-93c5-76b787af9497',
                                    'event_datetime' => '2020-01-01T00:00:00+00:00',
                                    'author' => 'julia',
                                    'author_type' => 'ui',
                                    'pim_source' => 'staging.akeneo.com',
                                    'data' => ['identifier' => '1'],
                                ],
                            ],
                        ], $this->sut->content());
    }

    public function test_it_returns_the_webhook(): void
    {
        $webhook = $this->createMock(ActiveWebhook::class);

        $this->sut = new WebhookRequest(
            $webhook,
            []
        );
        $this->assertSame($webhook, $this->sut->webhook());
    }

    public function test_it_returns_the_api_events(): void
    {
        $webhook = $this->createMock(ActiveWebhook::class);
        $webhookEvent = $this->createMock(WebhookEvent::class);

        $this->sut = new WebhookRequest(
            $webhook,
            [$webhookEvent]
        );
        $this->assertSame([$webhookEvent], $this->sut->apiEvents());
    }

    private function createEvent(Author $author, array $data): EventInterface
    {
        $timestamp = 1_577_836_800;
        $uuid = '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c';
    
        return new class($author, $data, $timestamp, $uuid) extends Event {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}

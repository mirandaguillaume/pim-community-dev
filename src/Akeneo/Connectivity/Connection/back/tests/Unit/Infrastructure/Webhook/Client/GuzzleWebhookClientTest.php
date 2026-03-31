<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Webhook\Client;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiRequestLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\SendApiEventRequestLogger;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClientInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestFailedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client\GuzzleWebhookClient;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Client\Signature;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\RequestHeaders;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GuzzleWebhookClientTest extends TestCase
{
    private SendApiEventRequestLogger|MockObject $sendApiEventRequestLogger;
    private EventsApiRequestLoggerInterface|MockObject $eventsApiRequestLogger;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private VersionProviderInterface|MockObject $versionProvider;
    private GuzzleWebhookClient $sut;

    protected function setUp(): void
    {
        $this->sendApiEventRequestLogger = $this->createMock(SendApiEventRequestLogger::class);
        $this->eventsApiRequestLogger = $this->createMock(EventsApiRequestLoggerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->versionProvider = $this->createMock(VersionProviderInterface::class);
        $this->sut = new GuzzleWebhookClient(
            new Client(),
            new JsonEncoder(),
            $this->sendApiEventRequestLogger,
            $this->eventsApiRequestLogger,
            $this->eventDispatcher,
            ['timeout' => 0.5, 'concurrency' => 1],
            $this->versionProvider,
            (\getenv('PFID') ?: null),
        );
        $this->eventDispatcher->method('dispatch')->willReturnArgument(0);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GuzzleWebhookClient::class, $this->sut);
        $this->assertInstanceOf(WebhookClientInterface::class, $this->sut);
    }

    public function test_it_sends_webhook_requests_in_bulk(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventsApiRequestLogger = $this->createMock(EventsApiRequestLoggerInterface::class);

        $this->versionProvider->method('getVersion')->willReturn('v20210526040645');
        $mock = new MockHandler(
            [
                        new Response(200, ['Content-Length' => 0]),
                        new Response(200, ['Content-Length' => 0]),
                    ]
        );
        $container = [];
        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $pfid = (\getenv('PFID') ?: null);
        $this->sut = new GuzzleWebhookClient(
            new Client(['handler' => $handlerStack]),
            new JsonEncoder(),
            $this->sendApiEventRequestLogger,
            $eventsApiRequestLogger,
            $eventDispatcher,
            ['timeout' => 0.5, 'concurrency' => 1],
            $this->versionProvider,
            $pfid,
        );
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $Request1pimEvent = $this->createEvent($author, ['data_1'], 1_577_836_800, '7abae2fe-759a-4fce-aa43-f413980671b3');
        $request1 = new WebhookRequest(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook1', false),
            [
                        new WebhookEvent(
                            'product.created',
                            '7abae2fe-759a-4fce-aa43-f413980671b3',
                            '2020-01-01T00:00:00+00:00',
                            $author,
                            'staging.akeneo.com',
                            ['data_1'],
                            $Request1pimEvent
                        ),
                    ]
        );
        $Request2pimEvent = $this->createEvent($author, ['data_2'], 1_577_836_800, '7abae2fe-759a-4fce-aa43-f413980671b3');
        $request2 = new WebhookRequest(
            new ActiveWebhook('erp', 1, 'a_secret', 'http://localhost/webhook2', false),
            [
                        new WebhookEvent(
                            'product.created',
                            '7abae2fe-759a-4fce-aa43-f413980671b3',
                            '2020-01-01T00:00:00+00:00',
                            $author,
                            'staging.akeneo.com',
                            ['data_2'],
                            $Request2pimEvent
                        ),
                    ]
        );

        $dispatchedEvents = [];
        $eventDispatcher->method('dispatch')->willReturnCallback(function ($event) use (&$dispatchedEvents) {
            $dispatchedEvents[] = $event;
            return $event;
        });

        $this->sut->bulkSend([$request1, $request2]);
        Assert::assertCount(2, $container);
        // Request 1

        $request = $this->findRequest($container, 'http://localhost/webhook1');
        Assert::assertNotNull($request);
        $body = '{"events":[{"action":"product.created","event_id":"7abae2fe-759a-4fce-aa43-f413980671b3","event_datetime":"2020-01-01T00:00:00+00:00","author":"julia","author_type":"ui","pim_source":"staging.akeneo.com","data":["data_1"]}]}';
        Assert::assertEquals($body, (string) $request->getBody());
        $timestamp = (int) $request->getHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)[0];
        $signature = Signature::createSignature('a_secret', $timestamp, $body);
        Assert::assertEquals($signature, $request->getHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)[0]);
        $userAgent = 'AkeneoPIM/v20210526040645';
        if (null !== $pfid) {
            $userAgent .= ' ' . $pfid;
        }
        Assert::assertSame($userAgent, $request->getHeader(RequestHeaders::HEADER_REQUEST_USERAGENT)[0]);

        // Request 2
        $request = $this->findRequest($container, 'http://localhost/webhook2');
        Assert::assertNotNull($request);
        $body = '{"events":[{"action":"product.created","event_id":"7abae2fe-759a-4fce-aa43-f413980671b3","event_datetime":"2020-01-01T00:00:00+00:00","author":"julia","author_type":"ui","pim_source":"staging.akeneo.com","data":["data_2"]}]}';
        Assert::assertEquals($body, (string) $request->getBody());
        $timestamp = (int) $request->getHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)[0];
        $signature = Signature::createSignature('a_secret', $timestamp, $body);
        Assert::assertEquals($signature, $request->getHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)[0]);

        // Verify dispatched events
        Assert::assertCount(2, $dispatchedEvents);
        Assert::assertInstanceOf(EventsApiRequestSucceededEvent::class, $dispatchedEvents[0]);
        Assert::assertInstanceOf(EventsApiRequestSucceededEvent::class, $dispatchedEvents[1]);
    }

    public function test_it_logs_a_failed_events_api_request(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventsApiRequestLogger = $this->createMock(EventsApiRequestLoggerInterface::class);

        $this->versionProvider->method('getVersion')->willReturn('v20210526040645');
        $mock = new MockHandler(
            [
                        new Response(500, ['Content-Length' => 0]),
                    ]
        );
        $container = [];
        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);
        $this->sut = new GuzzleWebhookClient(
            new Client(['handler' => $handlerStack]),
            new JsonEncoder(),
            $this->sendApiEventRequestLogger,
            $eventsApiRequestLogger,
            $eventDispatcher,
            ['timeout' => 0.5, 'concurrency' => 1],
            $this->versionProvider,
            (\getenv('PFID') ?: null),
        );
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $request1 = new WebhookRequest(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook1', false),
            [
                        new WebhookEvent(
                            'product.created',
                            '7abae2fe-759a-4fce-aa43-f413980671b3',
                            '2020-01-01T00:00:00+00:00',
                            $author,
                            'staging.akeneo.com',
                            ['data_1'],
                            $this->createEvent($author, ['data_1'], 1_577_836_800, '7abae2fe-759a-4fce-aa43-f413980671b3')
                        ),
                    ]
        );
        $eventsApiRequestLogger->expects($this->once())->method('logEventsApiRequestFailed')->with(
            'ecommerce',
            $request1->apiEvents(),
            'http://localhost/webhook1',
            500,
            $this->anything()
        );
        $eventDispatcher->expects($this->once())->method('dispatch')->with($this->isInstanceOf(EventsApiRequestFailedEvent::class))->willReturnArgument(0);
        $this->sut->bulkSend([$request1]);
        Assert::assertCount(1, $container);
    }

    public function test_it_does_not_send_webhook_request_because_of_timeout(): void
    {
        $debugLogger = $this->createMock(EventsApiRequestLoggerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->versionProvider->method('getVersion')->willReturn('v20210526040645');
        $container = [];
        $history = Middleware::history($container);
        $handlerStack = HandlerStack::create();
        $handlerStack->push($history);
        $this->sut = new GuzzleWebhookClient(
            new Client(['handler' => $handlerStack]),
            new JsonEncoder(),
            $this->sendApiEventRequestLogger,
            $debugLogger,
            $eventDispatcher,
            ['timeout' => 0.5, 'concurrency' => 1],
            $this->versionProvider,
            (\getenv('PFID') ?: null),
        );
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $webhookRequest = new WebhookRequest(
            new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/webhook', false),
            [
                        new WebhookEvent(
                            'product.created',
                            '7abae2fe-759a-4fce-aa43-f413980671b3',
                            '2020-01-01T00:00:00+00:00',
                            $author,
                            'staging.akeneo.com',
                            ['data_1'],
                            $this->createEvent($author, ['data_1'], 1_577_836_800, '7abae2fe-759a-4fce-aa43-f413980671b3')
                        ),
                    ]
        );
        $debugLogger->expects($this->once())->method('logEventsApiRequestTimedOut')->with(
            'ecommerce',
            $webhookRequest->apiEvents(),
            'http://localhost/webhook',
            0.5
        );
        $eventDispatcher->expects($this->once())->method('dispatch')->with($this->isInstanceOf(EventsApiRequestFailedEvent::class))->willReturnArgument(0);
        $this->sut->bulkSend([$webhookRequest]);
        Assert::assertCount(1, $container);
        $request = $this->findRequest($container, 'http://localhost/webhook');
        Assert::assertNotNull($request);
        $body = '{"events":[{"action":"product.created","event_id":"7abae2fe-759a-4fce-aa43-f413980671b3","event_datetime":"2020-01-01T00:00:00+00:00","author":"julia","author_type":"ui","pim_source":"staging.akeneo.com","data":["data_1"]}]}';
        Assert::assertEquals($body, (string) $request->getBody());
        $timestamp = (int) $request->getHeader(RequestHeaders::HEADER_REQUEST_TIMESTAMP)[0];
        $signature = Signature::createSignature('a_secret', $timestamp, $body);
        Assert::assertEquals($signature, $request->getHeader(RequestHeaders::HEADER_REQUEST_SIGNATURE)[0]);
    }

    private function findRequest(array $container, string $url): ?Request
    {
        foreach ($container as $transaction) {
            if ($url === (string) $transaction['request']->getUri()) {
                return $transaction['request'];
            }
        }
    
        return null;
    }

    private function createEvent(Author $author, array $data, int $timestamp, string $uuid): EventInterface
    {
        return new class($author, $data, $timestamp, $uuid) extends Event {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}

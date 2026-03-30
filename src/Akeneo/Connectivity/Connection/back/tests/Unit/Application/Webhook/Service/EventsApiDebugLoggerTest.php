<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\ApiEventBuildErrorLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventsApiDebugLogger;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventSubscriptionSkippedOwnEventLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\LimitOfEventsApiRequestsReachedLoggerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepositoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugLoggerTest extends TestCase
{
    private EventsApiDebugRepositoryInterface|MockObject $eventsApiDebugRepository;
    private EventsApiDebugLogger $sut;

    protected function setUp(): void
    {
        $this->eventsApiDebugRepository = $this->createMock(EventsApiDebugRepositoryInterface::class);
        $this->sut = new EventsApiDebugLogger(
            $this->eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            []
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventsApiDebugLogger::class, $this->sut);
    }

    public function test_it_logs_when_the_event_subscription_has_been_sent(): void
    {
        $this->sut = new EventsApiDebugLogger(
            $this->eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );
        $this->assertInstanceOf(EventSubscriptionSkippedOwnEventLoggerInterface::class, $this->sut);
        $this->eventsApiDebugRepository->expects($this->once())->method('persist')->with($this->callback(function ($actual): bool {
            if (!isset($actual['id']) || !\is_string($actual['id'])) {
                return false;
            }
            unset($actual['id']);
            return $actual === [
                'timestamp' => 1_609_459_200,
                'level' => 'info',
                'message' => 'The API event request was sent.',
                'connection_code' => 'erp_000',
                'context' => [
                    'event_subscription_url' => 'http://my-url.com',
                    'status_code' => 200,
                    'headers' => [],
                    'events' => [
                        [
                            "action" => "my_event",
                            "event_id" => "9979c367-595d-42ad-9070-05f62f31f49b",
                            "event_datetime" => "1970-01-01T00:00:00+00:00",
                            "author" => "julia",
                            "author_type" => "ui",
                        ],
                    ],
                ],
            ];
        }));
        $this->sut->logEventsApiRequestSucceed(
            'erp_000',
            [$this->createWebhookEvent()],
            'http://my-url.com',
            200,
            []
        );
    }

    public function test_it_logs_when_the_event_subscription_skipped_its_own_event(): void
    {
        $this->sut = new EventsApiDebugLogger(
            $this->eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );
        $this->assertInstanceOf(EventSubscriptionSkippedOwnEventLoggerInterface::class, $this->sut);
        $this->eventsApiDebugRepository->expects($this->once())->method('persist')->with($this->callback(function ($actual): bool {
            if (!isset($actual['id']) || !\is_string($actual['id'])) {
                return false;
            }
            unset($actual['id']);
            return $actual === [
                'timestamp' => 1_609_459_200,
                'level' => 'notice',
                'message' => 'The event was not sent because it was raised by the same connection.',
                'connection_code' => 'erp_000',
                'context' => [
                    'event' => [
                        'action' => 'my_event',
                        'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                        'event_datetime' => '1970-01-01T00:00:00+00:00',
                        'author' => 'julia',
                        'author_type' => 'ui',
                    ],
                ],
            ];
        }));
        $this->sut->logEventSubscriptionSkippedOwnEvent('erp_000', $this->createEvent());
    }

    public function test_it_logs_when_the_limit_of_event_api_requests_is_reached(): void
    {
        $this->sut = new EventsApiDebugLogger(
            $this->eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );
        $this->assertInstanceOf(LimitOfEventsApiRequestsReachedLoggerInterface::class, $this->sut);
        $this->eventsApiDebugRepository->expects($this->once())->method('persist')->with($this->callback(function ($actual): bool {
            if (!isset($actual['id']) || !\is_string($actual['id'])) {
                return false;
            }
            unset($actual['id']);
            return $actual === [
                'timestamp' => 1_609_459_200,
                'level' => 'warning',
                'message' => 'The maximum number of events sent per hour has been reached.',
                'connection_code' => null,
                'context' => [],
            ];
        }));
        $this->sut->logLimitOfEventsApiRequestsReached();
    }

    public function test_it_logs_when_the_resource_was_not_found_or_access_denied(): void
    {
        $this->sut = new EventsApiDebugLogger(
            $this->eventsApiDebugRepository,
            new FakeClock(new \DateTimeImmutable('2021-01-01T00:00:00+00:00')),
            [],
            1
        );
        $this->assertInstanceOf(ApiEventBuildErrorLoggerInterface::class, $this->sut);
        $this->eventsApiDebugRepository->expects($this->once())->method('persist')->with($this->callback(function ($actual): bool {
            if (!isset($actual['id']) || !\is_string($actual['id'])) {
                return false;
            }
            unset($actual['id']);
            return $actual === [
                'timestamp' => 1_609_459_200,
                'level' => 'notice',
                'message' => 'The event was not sent because the product does not exists or the connection does not have the required permissions.',
                'connection_code' => 'erp_000',
                'context' => [
                    'event' => [
                        'action' => 'my_event',
                        'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                        'event_datetime' => '1970-01-01T00:00:00+00:00',
                        'author' => 'julia',
                        'author_type' => 'ui',
                    ],
                ],
            ];
        }));
        $this->sut->logResourceNotFoundOrAccessDenied('erp_000', $this->createEvent());
    }

    private function createEvent(): EventInterface
    {
        return new class(Author::fromNameAndType('julia', Author::TYPE_UI), [], 0, '9979c367-595d-42ad-9070-05f62f31f49b') extends Event {
            public function getName(): string
            {
                return 'my_event';
            }
        };
    }

    private function createWebhookEvent(): WebhookEvent
    {
        return new WebhookEvent(
            'product.created',
            '9979c367-595d-42ad-9070-05f62f31f49b',
            '1970-01-01T00:00:00+00:00',
            Author::fromNameAndType('julia', Author::TYPE_UI),
            'ui',
            [],
            $this->createEvent()
        );
    }
}

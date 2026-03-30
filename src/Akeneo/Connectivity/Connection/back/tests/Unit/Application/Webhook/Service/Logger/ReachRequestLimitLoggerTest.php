<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook\Service\Logger;

use Akeneo\Connectivity\Connection\Application\Webhook\Log\EventSubscriptionEventBuildLog;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\Logger\ReachRequestLimitLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReachRequestLimitLoggerTest extends TestCase
{
    private LoggerInterface|MockObject $logger;
    private ReachRequestLimitLogger $sut;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new ReachRequestLimitLogger($this->logger);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ReachRequestLimitLogger::class, $this->sut);
    }

    public function test_it_logs_event_requests_limit_reached(): void
    {
        $expectedLog = [
                    'type' => 'event_api.reach_requests_limit',
                    'message' => 'event subscription requests limit has been reached',
                    'limit' => 666,
                    'retry_after_seconds' => 90,
                    'limit_reset' => '2021-01-01T00:01:30+00:00',
                ];
        $this->logger->expects($this->once())->method('info')->with(\json_encode($expectedLog, JSON_THROW_ON_ERROR));
        $this->sut->log(
            666,
            new \DateTimeImmutable('2021-01-01T00:00:00+00:00'),
            90
        )
        ;
    }
}

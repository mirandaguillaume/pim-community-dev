<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityHandler;
use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\WebhookReachabilityChecker;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckWebhookReachabilityHandlerTest extends TestCase
{
    private WebhookReachabilityChecker|MockObject $reachabilityChecker;
    private CheckWebhookReachabilityHandler $sut;

    protected function setUp(): void
    {
        $this->reachabilityChecker = $this->createMock(WebhookReachabilityChecker::class);
        $this->sut = new CheckWebhookReachabilityHandler($this->reachabilityChecker);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CheckWebhookReachabilityHandler::class, $this->sut);
    }

    public function test_it_returns_url_reachability_status(): void
    {
        $command = new CheckWebhookReachabilityCommand('http://172.17.0.1:8000/webhook', '1234');
        $expectedUrlReachabilityStatus = new UrlReachabilityStatus(true, "200: OK");
        $this->reachabilityChecker->method('check')->with($command->webhookUrl(), $command->secret())->willReturn($expectedUrlReachabilityStatus);
        $handleResult = $this->sut->handle($command);
        Assert::assertEquals($expectedUrlReachabilityStatus, $handleResult);
    }
}

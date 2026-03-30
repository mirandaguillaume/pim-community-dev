<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\CheckWebhookReachabilityCommand;
use PHPUnit\Framework\TestCase;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckWebhookReachabilityCommandTest extends TestCase
{
    private CheckWebhookReachabilityCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new CheckWebhookReachabilityCommand('http://172.17.0.1:8000/webhook', '1234');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CheckWebhookReachabilityCommand::class, $this->sut);
    }

    public function test_it_returns_the_webhook_url(): void
    {
        $this->assertSame('http://172.17.0.1:8000/webhook', $this->sut->webhookUrl());
    }

    public function test_it_returns_the_secret(): void
    {
        $this->assertSame('1234', $this->sut->secret());
    }
}

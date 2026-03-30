<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SendBusinessEventToWebhooksCommandTest extends TestCase
{
    private BulkEventInterface|MockObject $event;
    private SendBusinessEventToWebhooksCommand $sut;

    protected function setUp(): void
    {
        $this->event = $this->createMock(BulkEventInterface::class);
        $this->sut = new SendBusinessEventToWebhooksCommand($this->event);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SendBusinessEventToWebhooksCommand::class, $this->sut);
    }

    public function test_it_returns_the_business_event(): void
    {
        $this->assertSame($this->event, $this->sut->event());
    }
}

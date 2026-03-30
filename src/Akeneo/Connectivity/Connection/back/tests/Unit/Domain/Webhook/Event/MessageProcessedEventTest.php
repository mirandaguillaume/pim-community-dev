<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Webhook\Event;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MessageProcessedEventTest extends TestCase
{
    private MessageProcessedEvent $sut;

    protected function setUp(): void
    {
        $this->sut = new MessageProcessedEvent();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MessageProcessedEvent::class, $this->sut);
    }
}

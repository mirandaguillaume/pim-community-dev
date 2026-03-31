<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\GpsTransport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GpsTransportTest extends TestCase
{
    private Client|MockObject $client;
    private SenderInterface|MockObject $sender;
    private ReceiverInterface|MockObject $receiver;
    private GpsTransport $sut;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->sender = $this->createMock(SenderInterface::class);
        $this->receiver = $this->createMock(ReceiverInterface::class);
        $this->sut = new GpsTransport($this->client, $this->sender, $this->receiver);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GpsTransport::class, $this->sut);
    }

    public function test_it_is_a_transport(): void
    {
        $this->assertInstanceOf(TransportInterface::class, $this->sut);
    }

    public function test_it_is_setupable(): void
    {
        $this->assertInstanceOf(SetupableTransportInterface::class, $this->sut);
        $this->client->expects($this->once())->method('setup');
        $this->sut->setup();
    }

    public function test_it_sends_a_message(): void
    {
        $envelope = new Envelope(new \stdClass());
        $this->sender->expects($this->once())->method('send')->with($envelope)->willReturn($envelope);
        $this->sut->send($envelope);
    }

    public function test_it_gets_some_messages(): void
    {
        $envelopes = [];
        $this->receiver->method('get')->willReturn($envelopes);
        $this->assertSame($envelopes, $this->sut->get());
    }

    public function test_it_aknowledges_a_message(): void
    {
        $envelope = new Envelope(new \stdClass());
        $this->receiver->expects($this->once())->method('ack')->with($envelope);
        $this->sut->ack($envelope);
    }

    public function test_it_rejects_a_message(): void
    {
        $envelope = new Envelope(new \stdClass());
        $this->receiver->expects($this->once())->method('reject')->with($envelope);
        $this->sut->reject($envelope);
    }
}

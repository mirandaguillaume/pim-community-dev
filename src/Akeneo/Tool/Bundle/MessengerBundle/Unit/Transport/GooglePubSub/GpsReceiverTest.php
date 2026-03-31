<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\NativeMessageStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\GpsReceiver;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GpsReceiverTest extends TestCase
{
    private Subscription|MockObject $subscription;
    private SerializerInterface|MockObject $serializer;
    private GpsReceiver $sut;

    protected function setUp(): void
    {
        $this->subscription = $this->createMock(Subscription::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->sut = new GpsReceiver($this->subscription, $this->serializer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GpsReceiver::class, $this->sut);
    }

    public function test_it_gets_messages(): void
    {
        $gpsMessage = new Message(
            [
                        'data' => 'My message!',
                        'messageId' => '123',
                        'attributes' => [
                            'my_attribute' => 'My attribute!',
                            'tenant_id' => 'my_tenant_id_value',
                        ],
                    ]
        );
        $envelope = new Envelope((object) ['message' => 'My message!'], [new TenantIdStamp('my_tenant_id_value')]);
        $this->subscription->method('pull')->with([
                    'maxMessages' => 1,
                    'returnImmediately' => true,
                ])->willReturn([$gpsMessage]);
        $this->serializer->method('decode')->with([
                    'body' => 'My message!',
                    'headers' => [
                        'my_attribute' => 'My attribute!',
                        'tenant_id' => 'my_tenant_id_value',
                    ],
                ])->willReturn($envelope);
        $this->subscription->expects($this->never())->method('acknowledge')->with($gpsMessage);
        $this->assertEquals([
                        $envelope
                            ->with(new TransportMessageIdStamp('123'))->with(new NativeMessageStamp($gpsMessage)),
                    ], $this->sut->get());
    }

    public function test_it_acknowledges_a_message(): void
    {
        $gpsMessage = new Message(
            [
                        'data' => 'My message!',
                    ]
        );
        $envelope = new Envelope((object) ['message' => 'My message!'], [new NativeMessageStamp($gpsMessage)]);
        $this->subscription->expects($this->once())->method('acknowledge')->with($gpsMessage);
        $this->sut->ack($envelope);
    }

    public function test_it_modifies_ack_deadline(): void
    {
        $gpsMessage = new Message(['data' => 'My message!']);
        $envelope = new Envelope((object) ['message' => 'My message!'], [new NativeMessageStamp($gpsMessage)]);
        $this->subscription->expects($this->once())->method('modifyAckDeadline')->with($gpsMessage, 600);
        $this->sut->modifyAckDeadline($envelope);
    }

    public function test_it_rejects_a_message(): void
    {
        $gpsMessage = new Message(
            [
                        'data' => 'My message!',
                    ]
        );
        $envelope = new Envelope((object) ['message' => 'My message!'], [new NativeMessageStamp($gpsMessage)]);
        $this->subscription->expects($this->once())->method('acknowledge')->with($gpsMessage);
        $this->sut->reject($envelope);
    }

    public function test_it_throws_a_transport_exception_if_an_error_is_raised_while_fetching_a_message(): void
    {
        $this->subscription->method('pull')->with([
                    'maxMessages' => 1,
                    'returnImmediately' => true,
                ])->willThrowException(new GoogleException("test error"));
        $this->expectException(TransportException::class);
        iterator_to_array($this->sut->get());
    }

    public function test_it_throws_a_transport_exception_if_an_error_is_raised_while_acknowledging_a_message(): void
    {
        $gpsMessage = new Message(
            [
                        'data' => 'My message!',
                    ]
        );
        $envelope = new Envelope((object) ['message' => 'My message!'], [new NativeMessageStamp($gpsMessage)]);
        $this->subscription->method('acknowledge')->with($gpsMessage)->willThrowException(new GoogleException("test error"));
        $this->expectException(TransportException::class);
        $this->sut->ack($envelope);
    }

    public function test_it_throws_a_transport_exception_if_an_error_is_raised_while_rejecting_a_message(): void
    {
        $gpsMessage = new Message(
            [
                        'data' => 'My message!',
                    ]
        );
        $envelope = new Envelope((object) ['message' => 'My message!'], [new NativeMessageStamp($gpsMessage)]);
        $this->subscription->method('acknowledge')->with($gpsMessage)->willThrowException(new GoogleException("test error"));
        $this->expectException(TransportException::class);
        $this->sut->reject($envelope);
    }
}

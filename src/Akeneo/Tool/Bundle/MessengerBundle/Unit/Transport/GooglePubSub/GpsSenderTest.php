<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\GpsSender;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\PubSub\Topic;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GpsSenderTest extends TestCase
{
    private Topic|MockObject $topic;
    private SerializerInterface|MockObject $serializer;
    private OrderingKeySolver|MockObject $orderingKeySolver;
    private GpsSender $sut;

    protected function setUp(): void
    {
        $this->topic = $this->createMock(Topic::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->orderingKeySolver = $this->createMock(OrderingKeySolver::class);
        $this->sut = new GpsSender($this->topic, $this->serializer, $this->orderingKeySolver);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GpsSender::class, $this->sut);
    }

    public function test_it_sends_a_message(): void
    {
        $envelope = new Envelope((object) ['message' => 'My message!']);
        $this->serializer->method('encode')->with($envelope)->willReturn([
                        'body' => 'My message!',
                        'headers' => ['my_header' => 'my_header_value'],
                    ]);
        $this->orderingKeySolver->method('solve')->with($envelope)->willReturn(null);
        $this->topic->expects($this->once())->method('publish')->with([
                    'data' => 'My message!',
                    'attributes' => ['my_header' => 'my_header_value'],
                ]);
        $this->sut->send($envelope);
    }

    public function test_it_sends_a_message_with_ordering_key(): void
    {
        $envelope = new Envelope(new \stdClass());
        $this->serializer->method('encode')->with($envelope)->willReturn([
                    'body' => 'My message!',
                    'headers' => ['my_header' => 'my_header_value'],
                ]);
        $this->orderingKeySolver->method('solve')->with($envelope)->willReturn('a_key');
        $this->topic->expects($this->once())->method('publish')->with([
                    'data' => 'My message!',
                    'attributes' => ['my_header' => 'my_header_value'],
                    'orderingKey' => 'a_key',
                ]);
        $this->sut->send($envelope);
    }

    public function test_it_sends_a_message_with_tenant_id(): void
    {
        $envelope = new Envelope(new \stdClass(), [new TenantIdStamp('my_tenant_id_value')]);
        $this->serializer->method('encode')->with($envelope)->willReturn([
                    'body' => 'My message!',
                    'headers' => [
                        'my_header' => 'my_header_value',
                        'tenant_id' => 'my_tenant_id_value',
                    ],
                ]);
        $this->orderingKeySolver->method('solve')->with($envelope)->willReturn(null);
        $this->topic->expects($this->once())->method('publish')->with([
                    'data' => 'My message!',
                    'attributes' => [
                        'my_header' => 'my_header_value',
                        'tenant_id' => 'my_tenant_id_value',
                    ],
                ]);
        $this->sut->send($envelope);
    }

    public function test_it_throws_a_transport_exception_if_an_error_is_raised_while_sending_a_message(): void
    {
        $envelope = new Envelope((object) ['message' => 'My message!']);
        $this->serializer->method('encode')->with($envelope)->willReturn([
                        'body' => 'My message!',
                        'headers' => ['my_header' => 'my_header_value'],
                    ]);
        $this->topic->method('publish')->with([
                    'data' => 'My message!',
                    'attributes' => ['my_header' => 'my_header_value'],
                ])->willThrowException(new GoogleException("test error"));
        $this->expectException(TransportException::class);
        $this->sut->send($envelope);
    }
}

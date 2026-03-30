<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Serialization;

use Akeneo\Tool\Bundle\MessengerBundle\Serialization\JsonSerializer;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use Akeneo\Tool\Component\Messenger\Stamp\CustomHeaderStamp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JsonSerializerTest extends TestCase
{
    private NormalizerInterface|MockObject $normalizer;
    private DenormalizerInterface|MockObject $denormalizer;
    private JsonSerializer $sut;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->sut = new JsonSerializer([$this->normalizer, $this->denormalizer]);
        $this->normalizer->method('getSupportedTypes')->with($this->anything())->willReturn(['*' => false]);
        $this->denormalizer->method('getSupportedTypes')->with($this->anything())->willReturn(['*' => false]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(JsonSerializer::class, $this->sut);
    }

    public function test_it_decodes_an_envelope(): void
    {
        $encodedEnvelope = [
                    'body' => '{"some_property":"Some value!"}',
                    'headers' => [
                        'class' => \stdClass::class,
                    ],
                ];
        $this->denormalizer->method('supportsDenormalization')->with(['some_property' => 'Some value!'], \stdClass::class, 'json', [])->willReturn(true);
        $message = new \stdClass();
        $this->denormalizer->method('denormalize')->with(['some_property' => 'Some value!'], \stdClass::class, 'json', [])->willReturn($message);
        $expectedEnvelope = new Envelope($message);
        $this->assertEquals($expectedEnvelope, $this->sut->decode($encodedEnvelope));
    }

    public function test_it_encodes_an_envelope(): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message);
        $this->normalizer->method('supportsNormalization')->with($message, 'json', [])->willReturn(true);
        $this->normalizer->method('normalize')->with($message, 'json', [])->willReturn(['some_property' => 'Some value!']);
        $this->assertSame([
                        'body' => '{"some_property":"Some value!"}',
                        'headers' => ['class' => \stdClass::class],
                    ], $this->sut->encode($envelope));
    }

    public function test_it_encodes_an_envelope_with_tenant_id(): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message, [new TenantIdStamp('my_tenant_id_value')]);
        $this->normalizer->method('supportsNormalization')->with($message, 'json', [])->willReturn(true);
        $this->normalizer->method('normalize')->with($message, 'json', [])->willReturn(['some_property' => 'Some value!']);
        $this->assertSame([
                        'body' => '{"some_property":"Some value!"}',
                        'headers' => [
                            'class' => \stdClass::class,
                            'tenant_id' => 'my_tenant_id_value',
                        ],
                    ], $this->sut->encode($envelope));
    }

    public function test_it_encodes_an_envelope_with_retry(): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message, [
                    new RedeliveryStamp(5),
                ]);
        $this->normalizer->method('supportsNormalization')->with($message, 'json', [])->willReturn(true);
        $this->normalizer->method('normalize')->with($message, 'json', [])->willReturn(['some_property' => 'Some value!']);
        $this->assertSame([
                        'body' => '{"some_property":"Some value!"}',
                        'headers' => [
                            'class' => \stdClass::class,
                            'retry_count' => '5',
                        ],
                    ], $this->sut->encode($envelope));
    }

    public function test_it_encodes_an_envelope_with_custom_header(): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message, [
                    $this->buildCustomStamp(),
                ]);
        $this->normalizer->method('supportsNormalization')->with($message, 'json', [])->willReturn(true);
        $this->normalizer->method('normalize')->with($message, 'json', [])->willReturn(['some_property' => 'Some value!']);
        $this->assertSame([
                        'body' => '{"some_property":"Some value!"}',
                        'headers' => [
                            'customHeader' => 'customerHeaderValue',
                            'class' => \stdClass::class,
                        ],
                    ], $this->sut->encode($envelope));
    }

    public function test_it_throw_an_exception_when_the_same_custom_header_is_used_twice(): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message, [
                    $this->buildCustomStamp(),
                    $this->buildCustomStamp(),
                ]);
        $this->normalizer->method('supportsNormalization')->with($message, 'json', [])->willReturn(true);
        $this->normalizer->method('normalize')->with($message, 'json', [])->willReturn(['some_property' => 'Some value!']);
        $this->expectException(\LogicException::class);
        $this->sut->encode($envelope);
    }

    private function buildCustomStamp(): CustomHeaderStamp
    {
        return new class implements CustomHeaderStamp {
            public function header(): string
            {
                return 'customHeader';
            }
    
            public function value(): string
            {
                return 'customerHeaderValue';
            }
        };
    }
}

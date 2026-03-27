<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Component\EventQueue;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventNormalizer;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BulkEventNormalizerTest extends TestCase
{
    private BulkEventNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new BulkEventNormalizer(new EventNormalizer());
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(BulkEventNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_normalization_of_bulk_event(): void
    {
        $event = new BulkEvent([]);
        $this->assertSame(true, $this->sut->supportsNormalization($event));
    }

    public function test_it_does_not_support_normalization_of_non_bulk_event(): void
    {
        $object = new \stdClass();
        $this->assertSame(false, $this->sut->supportsNormalization($object));
    }

    public function test_it_normalizes_a_bulk_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = new class ($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        }
        ;
        $bulkEvent = new BulkEvent([$event]);
        $expected = [
            [
                'type' => \get_class($event),
                'name' => 'event_name',
                'author' => 'julia',
                'author_type' => 'ui',
                'data' => ['data'],
                'timestamp' => 0,
                'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
            ],
        ];
        $this->assertSame($expected, $this->sut->normalize($bulkEvent));
    }

    public function test_it_is_a_denormalizer(): void
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_denormalization_of_bulk_event(): void
    {
        $this->assertSame(true, $this->sut->supportsDenormalization([], BulkEvent::class));
    }

    public function test_it_does_not_support_denormalization_of_bulk_non_event(): void
    {
        $this->assertSame(false, $this->sut->supportsDenormalization([], \stdClass::class));
    }

    public function test_it_denormalizes_a_bulk_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = new class ($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        }
        ;
        $bulkEvent = new BulkEvent([$event]);
        $data = [
            [
                'type' => \get_class($event),
                'name' => 'event_name',
                'author' => 'julia',
                'author_type' => 'ui',
                'data' => ['data'],
                'timestamp' => 0,
                'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
            ],
        ];
        $this->assertEquals($bulkEvent, $this->sut->denormalize($data, BulkEvent::class));
    }
}

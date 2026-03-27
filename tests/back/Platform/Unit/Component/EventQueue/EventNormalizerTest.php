<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Component\EventQueue;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventNormalizerTest extends TestCase
{
    private EventNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new EventNormalizer();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_normalization_of_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = new class ($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        };
        $this->assertSame(true, $this->sut->supportsNormalization($event));
    }

    public function test_it_does_not_support_normalization_of_non_event(): void
    {
        $object = new \stdClass();
        $this->assertSame(false, $this->sut->supportsNormalization($object));
    }

    public function test_it_normalizes_an_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = new class ($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        };
        $expected = [
            'type' => \get_class($event),
            'name' => 'event_name',
            'author' => 'julia',
            'author_type' => 'ui',
            'data' => ['data'],
            'timestamp' => 0,
            'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
        ];
        $this->assertSame($expected, $this->sut->normalize($event));
    }

    public function test_it_is_a_denormalizer(): void
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_denormalization_of_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = new class ($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        };
        $this->assertSame(true, $this->sut->supportsDenormalization([], \get_class($event)));
    }

    public function test_it_does_not_support_denormalization_of_non_event(): void
    {
        $this->assertSame(false, $this->sut->supportsDenormalization([], \stdClass::class));
    }

    public function test_it_denormalizes_an_event(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = new class ($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        };
        $data = [
            'type' => \get_class($event),
            'name' => 'event_name',
            'author' => 'julia',
            'author_type' => 'ui',
            'data' => ['data'],
            'timestamp' => 0,
            'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
        ];
        $denormalizedEvent = $this->sut->denormalize($data, \get_class($event));
        $this->assertEquals($event, $denormalizedEvent);
    }

    public function test_it_does_not_denormalize_an_event_because_author_type_is_invalid(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $event = new class ($author, ['data'], 0, 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba') extends Event {
            public function getName(): string
            {
                return 'event_name';
            }
        };
        $data = [
            'type' => \get_class($event),
            'name' => 'event_name',
            'author' => 'author',
            'author_type' => 'not_an_author_type',
            'data' => ['data'],
            'timestamp' => 0,
            'uuid' => 'e0e4c95d-9646-40d7-be2b-d9b14fc0c6ba',
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->denormalize(
            $data,
            Event::class,
        );
    }
}

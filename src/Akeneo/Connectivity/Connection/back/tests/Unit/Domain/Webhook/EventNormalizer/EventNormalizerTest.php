<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Webhook\EventNormalizer;

use Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer\EventNormalizer;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventNormalizerTest extends TestCase
{
    private EventNormalizer $sut;

    protected function setUp(): void
    {
        date_default_timezone_set('UTC');
        $this->sut = new EventNormalizer();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EventNormalizer::class, $this->sut);
    }

    public function test_it_supports_an_event(): void
    {
        $event = $this->createMock(EventInterface::class);

        $this->assertSame(true, $this->sut->supports($event));
    }

    public function test_it_normalizes_an_event(): void
    {
        $event = new class(Author::fromNameAndType('julia', Author::TYPE_UI), [], 0, '9979c367-595d-42ad-9070-05f62f31f49b') extends Event {
            public function getName(): string
            {
                return 'my_event';
            }
        }
        ;
        $this->assertSame([
                    'action' => 'my_event',
                    'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                    'event_datetime' => '1970-01-01T00:00:00+00:00',
                    'author' => 'julia',
                    'author_type' => 'ui',
                ], $this->sut->normalize($event));
    }
}

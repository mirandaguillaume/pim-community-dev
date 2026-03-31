<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Webhook\Model;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WebhookEventTest extends TestCase
{
    private UserInterface|MockObject $user;
    private WebhookEvent $sut;

    protected function setUp(): void
    {
        $this->user = $this->createMock(UserInterface::class);
        $this->user->method('getUserIdentifier')->willReturn('julia');
        $this->user->method('getFirstName')->willReturn('Julia');
        $this->user->method('getLastName')->willReturn('Doe');
        $this->user->method('isApiUser')->willReturn(false);
        $author = Author::fromUser($this->user);
        $this->sut = new WebhookEvent(
            'product.created',
            '21f7f779-f094-4305-8ee4-65fdddd5a418',
            '2020-01-01T00:00:00+00:00',
            $author,
            'staging.akeneo.com',
            ['data'],
            $this->createEvent($author, ['data'])
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(WebhookEvent::class, $this->sut);
    }

    public function test_it_returns_an_action(): void
    {
        $this->assertSame('product.created', $this->sut->action());
    }

    public function test_it_returns_an_event_id(): void
    {
        $this->assertSame('21f7f779-f094-4305-8ee4-65fdddd5a418', $this->sut->eventId());
    }

    public function test_it_returns_an_event_date_time(): void
    {
        $this->assertSame('2020-01-01T00:00:00+00:00', $this->sut->eventDateTime());
    }

    public function test_it_returns_an_author_name(): void
    {
        $this->assertSame('julia', $this->sut->author()->name());
    }

    public function test_it_returns_an_author_type(): void
    {
        $this->assertSame('ui', $this->sut->author()->type());
    }

    public function test_it_returns_a_pim_source(): void
    {
        $this->assertSame('staging.akeneo.com', $this->sut->pimSource());
    }

    public function test_it_returns_data(): void
    {
        $this->assertSame(['data'], $this->sut->data());
    }

    private function createEvent(Author $author, array $data): EventInterface
    {
        $timestamp = 1_577_836_800;
        $uuid = '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c';
    
        return new class($author, $data, $timestamp, $uuid) extends Event {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}

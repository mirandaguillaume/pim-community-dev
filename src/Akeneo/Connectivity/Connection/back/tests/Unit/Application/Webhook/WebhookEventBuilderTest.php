<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\ApiEventBuildErrorLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\WebhookEventDataBuilderNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\Context;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventBuilderTest extends TestCase
{
    private EventDataBuilderInterface|MockObject $notSupportedEventDataBuilder;
    private EventDataBuilderInterface|MockObject $supportedEventDataBuilder;
    private ApiEventBuildErrorLoggerInterface|MockObject $apiEventBuildErrorLogger;
    private WebhookEventBuilder $sut;

    protected function setUp(): void
    {
        $this->notSupportedEventDataBuilder = $this->createMock(EventDataBuilderInterface::class);
        $this->supportedEventDataBuilder = $this->createMock(EventDataBuilderInterface::class);
        $this->apiEventBuildErrorLogger = $this->createMock(ApiEventBuildErrorLoggerInterface::class);
        $this->sut = new WebhookEventBuilder(
            [$this->notSupportedEventDataBuilder, $this->supportedEventDataBuilder],
            $this->apiEventBuildErrorLogger
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(WebhookEventBuilder::class, $this->sut);
    }

    public function test_it_builds_a_webhook_event(): void
    {
        $user = $this->createMock(UserInterface::class);

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $user->method('getId')->willReturn(10);
        $user->method('getUserIdentifier')->willReturn('ecommerce_0000');
        $collection = new EventDataCollection();
        $collection->setEventData($pimEvent, ['data']);
        $this->notSupportedEventDataBuilder->method('supports')->with($pimEventBulk)->willReturn(false);
        $this->supportedEventDataBuilder->method('supports')->with($pimEventBulk)->willReturn(true);
        $this->supportedEventDataBuilder->method('build')->with($pimEventBulk, new Context('ecommerce_0000', 10, true))->willReturn($collection);
        $this->assertEquals([
                        new WebhookEvent(
                            'product.created',
                            'a20832d1-a1e6-4f39-99ea-a1dd859faddb',
                            '2020-09-11T08:49:21+00:00',
                            $author,
                            'staging.akeneo.com',
                            ['data'],
                            $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb')
                        ),
                    ], $this->sut->build(
                        $pimEventBulk,
                        [
                        'pim_source' => 'staging.akeneo.com',
                        'user' => $user,
                        'connection_code' => 'ecommerce',
                        'is_using_uuid' => true,
                    ]
                    ));
    }

    public function test_it_does_not_build_a_webhook_event_when_an_error_has_occured(): void
    {
        $user = $this->createMock(UserInterface::class);

        $user->method('getId')->willReturn(1);
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $user->method('getId')->willReturn(10);
        $user->method('getUserIdentifier')->willReturn('ecommerce_0000');
        $collection = new EventDataCollection();
        $collection->setEventDataError($pimEvent, new \Exception());
        $this->notSupportedEventDataBuilder->method('supports')->with($pimEventBulk)->willReturn(false);
        $this->supportedEventDataBuilder->method('supports')->with($pimEventBulk)->willReturn(true);
        $this->supportedEventDataBuilder->method('build')->with($pimEventBulk, new Context('ecommerce_0000', 10, false))->willReturn($collection);
        $this->assertEquals([], $this->sut->build(
            $pimEventBulk,
            [
                        'pim_source' => 'staging.akeneo.com',
                        'user' => $user,
                        'connection_code' => 'ecommerce',
                        'is_using_uuid' => false,
                    ]
        ));
    }

    public function test_it_log_when_a_resource_is_not_found(): void
    {
        $user = $this->createMock(UserInterface::class);

        $user->method('getId')->willReturn(1);
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $user->method('getId')->willReturn(10);
        $user->method('getUserIdentifier')->willReturn('ecommerce_0000');
        $collection = new EventDataCollection();
        $collection->setEventDataError($pimEvent, new \Exception());
        $this->notSupportedEventDataBuilder->method('supports')->with($pimEventBulk)->willReturn(false);
        $this->supportedEventDataBuilder->method('supports')->with($pimEventBulk)->willReturn(true);
        $this->supportedEventDataBuilder->method('build')->with($pimEventBulk, new Context('ecommerce_0000', 10, false))->willReturn($collection);
        $this->apiEventBuildErrorLogger->expects($this->once())->method('logResourceNotFoundOrAccessDenied')->with(
            'ecommerce',
            $pimEvent
        );
        $this->sut->build(
            $pimEventBulk,
            [
                        'pim_source' => 'staging.akeneo.com',
                        'user' => $user,
                        'connection_code' => 'ecommerce',
                        'is_using_uuid' => false,
                    ]
        );
    }

    public function test_it_throws_an_error_if_the_business_event_is_not_supported(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->sut = new WebhookEventBuilder(
            [],
            $this->apiEventBuildErrorLogger
        );
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $this->expectException(WebhookEventDataBuilderNotFoundException::class);
        $this->sut->build(
            $pimEventBulk,
            [
                            'pim_source' => 'staging.akeneo.com',
                            'user' => $user,
                            'connection_code' => 'ecommerce',
                            'is_using_uuid' => true,
                        ],
        );
    }

    public function test_it_throws_an_exception_if_there_is_no_pim_source_in_context(): void
    {
        $user = $this->createMock(UserInterface::class);

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $expectedException = new \InvalidArgumentException('The required option "pim_source" is missing.');
        $this->expectException($expectedException);
        $this->sut->build(
            $pimEventBulk,
            [
                        'user' => $user,
                        'connection_code' => 'ecommerce',
                        'is_using_uuid' => true,
                    ],
        );
    }

    public function test_it_throws_an_exception_if_pim_source_is_null(): void
    {
        $user = $this->createMock(UserInterface::class);

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->build(
            $pimEventBulk,
            [
                        'user' => $user,
                        'pim_source' => null,
                        'connection_code' => 'ecommerce',
                        'is_using_uuid' => true,
                    ],
        );
    }

    public function test_it_throws_an_exception_if_there_is_no_user_in_context(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $expectedException = new \InvalidArgumentException('The required option "pim_source" is missing.');
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->build(
            $pimEventBulk,
            [
                        'pim_source' => 'staging.akeneo.com',
                        'connection_code' => 'ecommerce',
                        'is_using_uuid' => true,
                    ],
        );
    }

    public function test_it_throws_an_exception_if_user_is_null(): void
    {
        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->build(
            $pimEventBulk,
            [
                        'user' => null,
                        'pim_source' => 'staging.akeneo.com',
                        'connection_code' => 'ecommerce',
                        'is_using_uuid' => true,
                    ],
        );
    }

    public function test_it_throws_an_exception_if_there_is_no_connection_code_in_context(): void
    {
        $user = $this->createMock(UserInterface::class);

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->build(
            $pimEventBulk,
            [
                        'user' => $user,
                        'pim_source' => 'staging.akeneo.com',
                        'is_using_uuid' => true,
                    ],
        );
    }

    public function test_it_throws_an_exception_if_connection_code_is_null(): void
    {
        $user = $this->createMock(UserInterface::class);

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->build(
            $pimEventBulk,
            [
                        'user' => $user,
                        'pim_source' => 'staging.akeneo.com',
                        'connection_code' => null,
                        'is_using_uuid' => true,
                    ],
        );
    }

    public function test_it_throws_an_exception_if_there_is_no_is_using_uuid_in_context(): void
    {
        $user = $this->createMock(UserInterface::class);

        $author = Author::fromNameAndType('julia', Author::TYPE_UI);
        $pimEvent = $this->createEvent($author, ['data'], 1_599_814_161, 'a20832d1-a1e6-4f39-99ea-a1dd859faddb');
        $pimEventBulk = new BulkEvent([$pimEvent]);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->build(
            $pimEventBulk,
            [
                        'user' => $user,
                        'pim_source' => 'staging.akeneo.com',
                        'connection_code' => 'ecommerce',
                    ],
        );
    }

    private function createEvent(Author $author, array $data, int $timestamp, string $uuid): EventInterface
    {
        return new class($author, $data, $timestamp, $uuid) extends Event {
            public function getName(): string
            {
                return 'product.created';
            }
        };
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\SendBusinessEventToWebhooksHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\EventSubscriptionSkippedOwnEventLoggerInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookEventBuilder;
use Akeneo\Connectivity\Connection\Application\Webhook\WebhookUserAuthenticator;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookClientInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Client\WebhookRequest;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\WebhookEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SelectActiveWebhooksQueryInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\Event;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SendBusinessEventToWebhooksHandlerTest extends TestCase
{
    private SelectActiveWebhooksQueryInterface|MockObject $selectActiveWebhooksQuery;
    private WebhookUserAuthenticator|MockObject $webhookUserAuthenticator;
    private WebhookClientInterface|MockObject $client;
    private WebhookEventBuilder|MockObject $builder;
    private LoggerInterface|MockObject $logger;
    private EventSubscriptionSkippedOwnEventLoggerInterface|MockObject $eventSubscriptionSkippedOwnEventLogger;
    private SendBusinessEventToWebhooksHandler $sut;

    protected function setUp(): void
    {
        $this->selectActiveWebhooksQuery = $this->createMock(SelectActiveWebhooksQueryInterface::class);
        $this->webhookUserAuthenticator = $this->createMock(WebhookUserAuthenticator::class);
        $this->client = $this->createMock(WebhookClientInterface::class);
        $this->builder = $this->createMock(WebhookEventBuilder::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventSubscriptionSkippedOwnEventLogger = $this->createMock(EventSubscriptionSkippedOwnEventLoggerInterface::class);
        $this->sut = new SendBusinessEventToWebhooksHandler(
            $this->selectActiveWebhooksQuery,
            $this->webhookUserAuthenticator,
            $this->client,
            $this->builder,
            $this->logger,
            $this->eventSubscriptionSkippedOwnEventLogger,
            'staging.akeneo.com'
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SendBusinessEventToWebhooksHandler::class, $this->sut);
    }

    public function test_it_sends_message_to_webhooks(): void
    {
        $juliaUser = new User();
        $juliaUser->setId(0);
        $juliaUser->setUsername('julia');
        $juliaUser->setFirstName('Julia');
        $juliaUser->setLastName('Doe');
        $magentoUser = new User();
        $magentoUser->setId(42);
        $magentoUser->setUsername('magento_452');
        $magentoUser->setFirstName('magento_452');
        $magentoUser->setLastName('magento_452');
        $magentoUser->defineAsApiUser();
        $author = Author::fromUser($juliaUser);
        $pimEventBulk = new BulkEvent([
                    $this->createEvent($author, ['data']),
                ]);
        $command = new SendBusinessEventToWebhooksCommand($pimEventBulk);
        $webhook = new ActiveWebhook('ecommerce', 42, 'a_secret', 'http://localhost/', true);
        $this->selectActiveWebhooksQuery->method('execute')->willReturn([$webhook]);
        $this->webhookUserAuthenticator->method('authenticate')->with(42)->willReturn($magentoUser);
        $this->builder->method('build')->with(
            $pimEventBulk,
            [
                            'user' => $magentoUser,
                            'pim_source' => 'staging.akeneo.com',
                            'connection_code' => $webhook->connectionCode(),
                            'is_using_uuid' => $webhook->isUsingUuid(),
                        ]
        )->willReturn([
                            new WebhookEvent(
                                'product.created',
                                '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                '2020-01-01T00:00:00+00:00',
                                $author,
                                'staging.akeneo.com',
                                ['data'],
                                $this->createEvent($author, ['data'])
                            ),
                        ]);
        $this->client->expects($this->once())->method('bulkSend')->with($this->callback(
            function (iterable $iterable): bool {
                $requests = \iterator_to_array($iterable);

                Assert::assertCount(1, $requests);
                Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);

                Assert::assertEquals('http://localhost/', $requests[0]->url());
                Assert::assertEquals('a_secret', $requests[0]->secret());
                Assert::assertEquals(
                    [
                        'events' => [
                            [
                                'action' => 'product.created',
                                'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                'event_datetime' => '2020-01-01T00:00:00+00:00',
                                'author' => 'julia',
                                'author_type' => 'ui',
                                'pim_source' => 'staging.akeneo.com',
                                'data' => ['data'],
                            ],
                        ],
                    ],
                    $requests[0]->content(),
                );

                return true;
            }
        ));
        $this->sut->handle($command);
    }

    public function test_it_does_not_send_the_message_if_the_webhook_is_the_author_of_the_business_event(): void
    {
        $erpUser = new User();
        $erpUser->setId(42);
        $erpUser->setUsername('erp_452');
        $erpUser->setFirstName('erp_452');
        $erpUser->setLastName('erp_452');
        $erpUser->defineAsApiUser();
        $magentoUser = new User();
        $magentoUser->setId(12);
        $magentoUser->setUsername('magento_987');
        $magentoUser->setFirstName('magento_987');
        $magentoUser->setLastName('magento_987');
        $magentoUser->defineAsApiUser();
        $erpAuthor = Author::fromUser($erpUser);
        $pimEventBulk = new BulkEvent([
                    $this->createEvent($erpAuthor, ['data']),
                ]);
        $command = new SendBusinessEventToWebhooksCommand($pimEventBulk);
        $erpWebhook = new ActiveWebhook('erp_source', 42, 'a_secret', 'http://localhost/', true);
        $magentoWebhook = new ActiveWebhook('ecommerce_destination', 12, 'a_secret', 'http://localhost/', false);
        $this->selectActiveWebhooksQuery->method('execute')->willReturn([$erpWebhook, $magentoWebhook]);
        $this->webhookUserAuthenticator->method('authenticate')->with(12)->willReturn($magentoUser);
        $this->webhookUserAuthenticator->method('authenticate')->with(42)->willReturn($erpUser);
        $this->builder->method('build')->with(
            $pimEventBulk,
            [
                            'pim_source' => 'staging.akeneo.com',
                            'user' => $magentoUser,
                            'connection_code' => $magentoWebhook->connectionCode(),
                            'is_using_uuid' => $magentoWebhook->isUsingUuid(),
                        ]
        )->willReturn([
                            new WebhookEvent(
                                'product.created',
                                '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                '2020-01-01T00:00:00+00:00',
                                $erpAuthor,
                                'staging.akeneo.com',
                                ['data'],
                                $this->createEvent($erpAuthor, ['data'])
                            ),
                        ]);
        $this->client->expects($this->once())->method('bulkSend')->with($this->callback(
            function (iterable $iterable): bool {
                $requests = \iterator_to_array($iterable);

                Assert::assertCount(1, $requests);
                Assert::assertContainsOnlyInstancesOf(WebhookRequest::class, $requests);

                Assert::assertEquals('http://localhost/', $requests[0]->url(), 'Url is not equal');
                Assert::assertEquals('a_secret', $requests[0]->secret(), 'Secret is not equal');
                Assert::assertEquals(
                    [
                        'events' => [
                            [
                                'action' => 'product.created',
                                'event_id' => '5d30d0f6-87a6-45ad-ba6b-3a302b0d328c',
                                'event_datetime' => '2020-01-01T00:00:00+00:00',
                                'author' => 'erp_452',
                                'author_type' => 'api',
                                'pim_source' => 'staging.akeneo.com',
                                'data' => ['data'],
                            ],
                        ],
                    ],
                    $requests[0]->content(),
                    'Content is not equal',
                );

                return true;
            }
        ));
        $this->sut->handle($command);
    }

    public function test_it_handles_error_gracefully(): void
    {
        $user = new User();
        $user->setId(0);
        $user->setUsername('julia');
        $user->setFirstName('Julia');
        $user->setLastName('Doe');
        $author = Author::fromUser($user);
        $pimEventBulk = new BulkEvent([
                    $this->createEvent($author, ['data']),
                ]);
        $command = new SendBusinessEventToWebhooksCommand($pimEventBulk);
        $webhook = new ActiveWebhook('ecommerce', 0, 'a_secret', 'http://localhost/', false);
        $this->selectActiveWebhooksQuery->method('execute')->willReturn([$webhook]);
        $this->webhookUserAuthenticator->method('authenticate')->with(0)->willReturn($user);
        $this->builder->method('build')->with(
            $pimEventBulk,
            [
                            'pim_source' => 'staging.akeneo.com',
                            'user' => $user,
                            'connection_code' => $webhook->connectionCode(),
                            'is_using_uuid' => $webhook->isUsingUuid(),
                        ]
        )->willThrowException(\Exception::class);
        $this->client->expects($this->once())->method('bulkSend')->with($this->callback(
            function (iterable $iterable): bool {
                $requests = \iterator_to_array($iterable);

                Assert::assertCount(0, $requests);

                return true;
            }
        ));
        $this->sut->handle($command);
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

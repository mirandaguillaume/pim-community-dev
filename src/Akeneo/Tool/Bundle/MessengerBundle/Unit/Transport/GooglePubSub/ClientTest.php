<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub;

use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\Client;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientTest extends TestCase
{
    private const PROJECT_ID = 'test-project';
    private const TOPIC_NAME = 'test-topic';
    private const SUBSCRIPTION_NAME = 'test-subscription';
    private PubSubClientFactory|MockObject $pubSubClientFactory;
    private PubSubClient|MockObject $pubSubClient;
    private Topic|MockObject $topic;
    private Subscription|MockObject $subscription;
    private Client $sut;

    protected function setUp(): void
    {
        $this->pubSubClientFactory = $this->createMock(PubSubClientFactory::class);
        $this->pubSubClient = $this->createMock(PubSubClient::class);
        $this->topic = $this->createMock(Topic::class);
        $this->subscription = $this->createMock(Subscription::class);
        $this->sut = new Client(
            $this->pubSubClientFactory,
            self::PROJECT_ID,
            self::TOPIC_NAME,
            self::SUBSCRIPTION_NAME
        );
        $this->pubSubClientFactory->method('createPubSubClient')->with(['projectId' => self::PROJECT_ID])->willReturn($this->pubSubClient);
        $this->pubSubClient->method('topic')->with(self::TOPIC_NAME)->willReturn($this->topic);
        $this->topic->method('subscription')->with(self::SUBSCRIPTION_NAME)->willReturn($this->subscription);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Client::class, $this->sut);
    }

    public function test_it_is_initializable_from_a_dsn(): void
    {
        $this->sut = Client::fromDsn(
            $this->pubSubClientFactory,
            'gps:',
            [
                        'project_id' => self::PROJECT_ID,
                        'topic_name' => self::TOPIC_NAME,
                        'subscription_name' => self::SUBSCRIPTION_NAME,
                        'auto_setup' => false,
                    ],
        );
        $this->pubSubClientFactory->method('createPubSubClient')->with(['projectId' => self::PROJECT_ID])->willReturn($this->pubSubClient);
        $this->pubSubClient->method('topic')->with(self::TOPIC_NAME)->willReturn($this->topic);
        $this->topic->method('subscription')->with(self::SUBSCRIPTION_NAME)->willReturn($this->subscription);
        $this->assertInstanceOf(Client::class, $this->sut);
    }

    public function test_it_can_be_setup(): void
    {
        $this->topic->method('exists')->willReturn(false);
        $this->subscription->expects($this->once())->method('reload');
        $this->subscription->method('exists')->willReturn(false);
        $this->topic->expects($this->once())->method('create');
        $this->subscription->expects($this->once())->method('create')->with([]);
        $this->sut->setup();
    }

    public function test_it_can_be_setup_with_a_subscription_filter(): void
    {
        $this->sut = Client::fromDsn(
            $this->pubSubClientFactory,
            'gps:dsn',
            [
                        'project_id' => self::PROJECT_ID,
                        'topic_name' => self::TOPIC_NAME,
                        'subscription_name' => self::SUBSCRIPTION_NAME,
                        'subscription_filter' => 'the_filter',
                        'auto_setup' => true,
                    ],
        );
        $this->topic->method('exists')->willReturn(false);
        $this->subscription->expects($this->once())->method('reload');
        $this->subscription->method('exists')->willReturn(false);
        $this->topic->expects($this->once())->method('create');
        $this->subscription->expects($this->once())->method('create')->with(['filter' => 'the_filter']);
        $this->sut->setup();
    }

    public function test_it_can_be_setup_without_subscription(): void
    {
        $this->sut = Client::fromDsn(
            $this->pubSubClientFactory,
            'gps:dsn',
            [
                        'project_id' => self::PROJECT_ID,
                        'topic_name' => self::TOPIC_NAME,
                        'subscription_name' => null,
                        'subscription_filter' => 'the_filter',
                        'auto_setup' => true,
                    ],
        );
        $this->topic->method('exists')->willReturn(false);
        $this->topic->expects($this->once())->method('create');
        $this->sut->setup();
    }

    public function test_it_cannot_be_setup_with_a_invalid_project_id(): void
    {
        $this->expectException(InvalidOptionsException::class);
        Client::fromDsn(
            $this->pubSubClientFactory,
            'gps:dsn',
            [
                        'project_id' => 10,
                        'topic_name' => self::TOPIC_NAME,
                        'subscription_name' => self::SUBSCRIPTION_NAME,
                        'subscription_filter' => 'the_filter',
                        'auto_setup' => true,
                    ],
        );
    }

    public function test_it_returns_the_topic(): void
    {
        $this->assertSame($this->topic, $this->sut->getTopic());
    }

    public function test_it_returns_the_subscription(): void
    {
        $this->assertSame($this->subscription, $this->sut->getSubscription());
    }
}

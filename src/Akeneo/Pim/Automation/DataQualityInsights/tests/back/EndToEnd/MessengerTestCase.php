<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\EndToEnd;

use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class MessengerTestCase extends DataQualityInsightsTestCase
{
    private const MESSENGER_COMMAND_NAME = 'messenger:consume';

    /**
     * Number of consecutive empty polls before concluding the queue is truly empty.
     * The PubSub emulator with returnImmediately=true can return empty results even
     * when messages exist, so we need multiple consecutive confirmations.
     */
    private const FLUSH_EMPTY_POLLS_REQUIRED = 3;

    /**
     * Delay in microseconds between flush poll attempts to allow in-flight messages
     * to be delivered to the subscription.
     */
    private const FLUSH_POLL_DELAY_US = 100_000; // 100ms

    /** @var PubSubQueueStatus[] */
    protected array $pubSubQueueStatuses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushQueues();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->flushQueues();
    }

    /**
     * Flush all messages from the configured PubSub queues.
     *
     * This method uses multiple consecutive empty polls to guard against the
     * PubSub emulator's unreliable returnImmediately behavior, which can
     * return no messages even when messages are pending delivery.
     */
    protected function flushQueues(): void
    {
        foreach ($this->pubSubQueueStatuses as $pubSubStatus) {
            $subscription = $pubSubStatus->getSubscription();
            try {
                $subscription->reload();
            } catch (\Exception) {
            }
            if (!$subscription->exists()) {
                continue;
            }

            $consecutiveEmptyPolls = 0;
            while ($consecutiveEmptyPolls < self::FLUSH_EMPTY_POLLS_REQUIRED) {
                $messages = $subscription->pull(['maxMessages' => 10, 'returnImmediately' => true]);
                $count = is_countable($messages) ? count($messages) : 0;
                if ($count > 0) {
                    $subscription->acknowledgeBatch($messages);
                    $consecutiveEmptyPolls = 0;
                } else {
                    $consecutiveEmptyPolls++;
                    if ($consecutiveEmptyPolls < self::FLUSH_EMPTY_POLLS_REQUIRED) {
                        usleep(self::FLUSH_POLL_DELAY_US);
                    }
                }
            }
        }
    }

    protected function dispatchMessage(object $message): void
    {
        $this->get('messenger.bus.default')->dispatch($message);
    }

    /**
     * Launch a Symfony Messenger consumer as a subprocess.
     *
     * The time-limit is set to 10 seconds to give the PubSub emulator enough
     * time to deliver messages, since returnImmediately=true in the GpsReceiver
     * can cause the consumer to poll multiple times before finding a message.
     */
    protected function launchConsumer(string $consumerName, int $limit = 1): void
    {
        $command = [
            \sprintf('%s/bin/console', $this->getParameter('kernel.project_dir')),
            self::MESSENGER_COMMAND_NAME,
            \sprintf('--env=%s', $this->getParameter('kernel.environment')),
            \sprintf('--limit=%d', $limit),
            '-vvv',
            \sprintf('--time-limit=%d', 10),
            $consumerName,
            '--bus=pim_event.handle.bus'
        ];

        $process = new Process($command);
        $process->run();
        $process->wait();

        Assert::assertSame(0, $process->getExitCode(), 'An error occurred: ' . $process->getErrorOutput());
    }

    /**
     * Consume all messages from a queue by repeatedly launching the consumer
     * until no more messages are found.
     *
     * Uses multiple consecutive checks with delays to guard against the PubSub
     * emulator's unreliable returnImmediately behavior, which can falsely report
     * an empty queue.
     */
    protected function consumeAllMessages(string $consumerName, PubSubQueueStatus $queueStatus): void
    {
        $consecutiveEmptyPolls = 0;
        $maxEmptyPolls = 3;

        while ($consecutiveEmptyPolls < $maxEmptyPolls) {
            if ($queueStatus->hasMessageInQueue()) {
                $this->launchConsumer($consumerName);
                $consecutiveEmptyPolls = 0;
            } else {
                $consecutiveEmptyPolls++;
                if ($consecutiveEmptyPolls < $maxEmptyPolls) {
                    usleep(200_000); // 200ms between polls
                }
            }
        }
    }
}

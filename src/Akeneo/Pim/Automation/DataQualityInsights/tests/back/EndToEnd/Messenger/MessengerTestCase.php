<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\EndToEnd\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

abstract class MessengerTestCase extends DataQualityInsightsTestCase
{
    private const MESSENGER_COMMAND_NAME = 'messenger:consume';

    /**
     * The queues prepared before and emptied around each test. A subclass adds the queue it
     * consumes by assigning this before calling parent::setUp(); the two below are appended, never
     * substituted. It was private and overwritten here, so a subclass's assignment created a
     * separate dynamic property that nothing read, and the subclass's queue was never handled.
     *
     * @var PubSubQueueStatus[]
     */
    protected array $pubSubQueueStatuses = [];

    private string $lastConsumerOutput = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->pubSubQueueStatuses = [
            ...$this->pubSubQueueStatuses,
            $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_launch_product_evaluations_consumer'),
            $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_attribute_group_activate_consumer'),
        ];

        // A PubSub subscription only receives what is published after it exists, and nothing creates
        // it before the consumer runs: the producer transport does not, and launchConsumer() comes
        // after the upsert. On a fresh emulator -- CI -- the test's own first message was therefore
        // lost, which is why the after_creation tests failed ("false is true" after consuming)
        // while passing locally, where the emulator keeps its subscriptions between runs.
        // EvaluateAfterAttributeGroupActivateEndToEnd already did this by hand.
        foreach ($this->pubSubQueueStatuses as $pubSubQueueStatus) {
            $pubSubQueueStatus->createTopicAndSubscription();
        }

        $this->flushQueues();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->flushQueues();
    }

    private function flushQueues(): void
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

            do {
                $messages = $subscription->pull(['maxMessages' => 10, 'returnImmediately' => true]);
                $count = is_countable($messages) ? count($messages) : 0;
                if ($count > 0) {
                    $subscription->acknowledgeBatch($messages);
                }
            } while (0 < $count);
        }
    }

    protected function dispatchMessage(object $message): void
    {
        $this->get('messenger.bus.default')->dispatch($message);
    }

    protected function launchConsumer(string $consumerName, int $limit = 1): void
    {
        $command = [
            \sprintf('%s/bin/console', $this->getParameter('kernel.project_dir')),
            self::MESSENGER_COMMAND_NAME,
            \sprintf('--env=%s', $this->getParameter('kernel.environment')),
            \sprintf('--limit=%d', $limit),
            '-vvv',
            \sprintf('--time-limit=%d', 5),
            $consumerName,
            '--bus=pim_event.handle.bus',
        ];

        $process = new Process($command);
        $process->run();
        $process->wait();
        $this->lastConsumerOutput = $process->getOutput() . $process->getErrorOutput();

        Assert::assertSame(0, $process->getExitCode(), 'An error occurred: ' . $process->getErrorOutput());
    }

    /**
     * TEMPORARY DIAGNOSTIC (#537): the after_creation tests fail on CI only, and two fixes based on
     * a reading of the code did not change that. This says, on failure, which step of the chain
     * broke -- was a message received, was it handled, were criteria evaluated, which score rows
     * exist and for which date -- so the next fix rests on what CI saw rather than on a guess.
     */
    protected function scoreDiagnostics(string $scoreTable, string $evaluationTable, string $idColumn, string|int $id): string
    {
        $connection = $this->get('database_connection');
        $evaluationIdColumn = 'product_model_id' === $idColumn ? 'product_id' : $idColumn;

        return \sprintf(
            "\n--- PHP date: %s, MySQL CURDATE(): %s\n--- %s rows: %s\n--- %s rows: %s\n--- consumer output (tail):\n%s",
            new \DateTimeImmutable('now')->format('Y-m-d H:i:s T'),
            $connection->fetchOne('SELECT CURDATE()'),
            $scoreTable,
            \json_encode($connection->fetchAllAssociative(
                \sprintf('SELECT evaluated_at FROM %s WHERE %s = :id', $scoreTable, $idColumn),
                ['id' => $id]
            )),
            $evaluationTable,
            \json_encode($connection->fetchAllAssociative(
                \sprintf('SELECT criterion_code, status, evaluated_at FROM %s WHERE %s = :id', $evaluationTable, $evaluationIdColumn),
                ['id' => $id]
            )),
            \substr($this->lastConsumerOutput, -4000)
        );
    }

    protected function assertProductScoreIsComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertTrue(
            $this->isProductScoreComputed($productUuid, $evaluatedAt),
            \sprintf('Product evaluation does not exist. Product uuid: %s', $productUuid->__toString())
        );
    }

    protected function assertProductScoreIsNotComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertFalse(
            $this->isProductScoreComputed($productUuid, $evaluatedAt),
            \sprintf('Product evaluation exists, it should not. Product uuid: %s', $productUuid->__toString())
        );
    }

    protected function isProductScoreComputed(
        ProductUuid $productUuid,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): bool {
        return (bool) $this->get('database_connection')->executeQuery(
            <<<SQL
                    SELECT product_uuid
                    FROM pim_data_quality_insights_product_score
                    WHERE product_uuid = :product_uuid AND evaluated_at = :evaluated_at
                SQL,
            ['product_uuid' => $productUuid->toBytes(), 'evaluated_at' => $evaluatedAt->format('Y-m-d')]
        )->fetchOne();
    }

    protected function assertProductModelScoreIsComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertTrue(
            $this->isProductModelScoreComputed($productModelId, $evaluatedAt),
            \sprintf('Product model evaluation does not exist. Product uuid: %s', $productModelId->__toString())
        );
    }

    protected function assertProductModelScoreIsNotComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): void {
        self::assertFalse(
            $this->isProductModelScoreComputed($productModelId, $evaluatedAt),
            \sprintf('Product model evaluation exists, it should not. Product model id: %s', $productModelId->__toString())
        );
    }

    protected function isProductModelScoreComputed(
        ProductModelId $productModelId,
        \DateTimeImmutable $evaluatedAt = new \DateTimeImmutable('now')
    ): bool {
        return (bool) $this->get('database_connection')->executeQuery(
            <<<SQL
                    SELECT product_model_id
                    FROM pim_data_quality_insights_product_model_score
                    WHERE product_model_id = :product_model_id AND evaluated_at = :evaluated_at
                SQL,
            ['product_model_id' => $productModelId->toInt(), 'evaluated_at' => $evaluatedAt->format('Y-m-d')]
        )->fetchOne();
    }
}

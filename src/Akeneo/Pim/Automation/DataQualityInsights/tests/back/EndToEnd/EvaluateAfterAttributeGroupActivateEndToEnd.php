<?php
declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\EndToEnd;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Command\UpdateAttributeGroupActivationCommand;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Command\UpdateAttributeGroupActivationHandler;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateAfterAttributeGroupActivateEndToEnd extends MessengerTestCase
{
    private PubSubQueueStatus $launchProductEvaluationsQueueStatus;
    private UpdateAttributeGroupActivationHandler $updateAttributeGroupActivationHandler;

    protected function setUp(): void
    {
        $this->launchProductEvaluationsQueueStatus = $this->get('akeneo_integration_tests.pub_sub_queue_status.dqi_launch_product_evaluations_consumer');
        $this->pubSubQueueStatuses = [
            $this->launchProductEvaluationsQueueStatus,
        ];

        // Ensure PubSub topics and subscriptions exist before any messages are dispatched.
        // The synchronous EvaluateAfterAttributeGroupActivateHandler publishes messages to the
        // launch_product_evaluations topic via the producer transport, which only creates the topic.
        // The subscription must already exist for those messages to be delivered to it.
        $this->launchProductEvaluationsQueueStatus->createTopicAndSubscription();

        parent::setUp();

        $this->updateAttributeGroupActivationHandler = $this->get(UpdateAttributeGroupActivationHandler::class);
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('system');

        $this->createAttribute('name');
        $this->createAttribute('width');
        $this->createAttribute('desc');
        $this->createAttribute('author');
        $this->createAttribute('other');
        $this->createAttributeGroup('changed_group1', ['attributes' => ['name']]);
        $this->createAttributeGroup('changed_group2', ['attributes' => ['desc']]);
        $this->createAttributeGroup('unchanged_group1', ['attributes' => ['author']]);
        $this->createAttributeGroup('unchanged_group2', ['attributes' => ['other']]);
        $this->createFamily('impacted_family1', ['attributes' => ['name', 'width', 'author']])->getId();
        $this->createFamily('impacted_family2', ['attributes' => ['desc']])->getId();
        $this->createFamily('not_impacted_family1', ['attributes' => ['author']])->getId();
        $this->createFamily('not_impacted_family2', ['attributes' => ['other']])->getId();

        ($this->updateAttributeGroupActivationHandler)(new UpdateAttributeGroupActivationCommand('changed_group1', true));
        ($this->updateAttributeGroupActivationHandler)(new UpdateAttributeGroupActivationCommand('changed_group2', false));
        ($this->updateAttributeGroupActivationHandler)(new UpdateAttributeGroupActivationCommand('unchanged_group1', false));
        ($this->updateAttributeGroupActivationHandler)(new UpdateAttributeGroupActivationCommand('unchanged_group2', true));

        $this->flushQueues();
    }

    public function test_it_recompute_product_scores_impacted_by_attribute_group_activation(): void
    {
        $product1Uuid = $this->createProduct('sku1', [new SetFamily('impacted_family1')])->getUuid();
        $product2Uuid = $this->createProduct('sku2', [new SetFamily('impacted_family2')])->getUuid();
        $product3Uuid = $this->createProduct('sku3', [new SetFamily('not_impacted_family1')])->getUuid();
        $product4Uuid = $this->createProduct('sku4', [new SetFamily('not_impacted_family2')])->getUuid();

        $this->assertProductScoreIsNotComputed(ProductUuid::fromUuid($product1Uuid));
        $this->assertProductScoreIsNotComputed(ProductUuid::fromUuid($product2Uuid));
        $this->assertProductScoreIsNotComputed(ProductUuid::fromUuid($product3Uuid));
        $this->assertProductScoreIsNotComputed(ProductUuid::fromUuid($product4Uuid));

        // Changed activation
        ($this->updateAttributeGroupActivationHandler)(new UpdateAttributeGroupActivationCommand('changed_group1', false));
        ($this->updateAttributeGroupActivationHandler)(new UpdateAttributeGroupActivationCommand('changed_group2', true));
        // Unchanged activation
        ($this->updateAttributeGroupActivationHandler)(new UpdateAttributeGroupActivationCommand('unchanged_group1', false));
        ($this->updateAttributeGroupActivationHandler)(new UpdateAttributeGroupActivationCommand('unchanged_group2', true));

        // The EvaluateAfterAttributeGroupActivateHandler runs synchronously (no transport
        // routing for AttributeGroupActivationHasChanged) and dispatches
        // LaunchProductAndProductModelEvaluationsMessage to the launch_product_evaluations
        // PubSub topic. We consume all messages from that queue.
        $this->consumeAllMessages('dqi_launch_product_evaluations', $this->launchProductEvaluationsQueueStatus);

        $this->assertProductScoreIsComputed(ProductUuid::fromUuid($product1Uuid));
        $this->assertProductScoreIsComputed(ProductUuid::fromUuid($product2Uuid));
        $this->assertProductScoreIsNotComputed(ProductUuid::fromUuid($product3Uuid));
        $this->assertProductScoreIsNotComputed(ProductUuid::fromUuid($product4Uuid));
    }

    public function test_it_recompute_product_model_scores_impacted_by_attribute_group_activation(): void
    {
        $axis = $this->createSimpleSelectAttributeWithOptions(self::MINIMAL_VARIANT_AXIS_CODE, self::MINIMAL_VARIANT_OPTIONS);
        $this->createFamily('fm', ['attributes' => [$axis->getCode(), 'name']]);
        $this->createFamilyVariant('fm_variant_1', 'fm', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => [$axis->getCode()],
                    'attributes' => [],
                ],
            ],
        ]);
        $pm = $this->createProductModel('pm1', 'fm_variant_1');

        $this->assertProductModelScoreIsNotComputed(ProductModelId::fromString((string) $pm->getId()));

        ($this->updateAttributeGroupActivationHandler)(new UpdateAttributeGroupActivationCommand('changed_group1', false));

        // The EvaluateAfterAttributeGroupActivateHandler runs synchronously and dispatches
        // LaunchProductAndProductModelEvaluationsMessage to the launch_product_evaluations
        // PubSub topic. We consume all messages from that queue.
        $this->consumeAllMessages('dqi_launch_product_evaluations', $this->launchProductEvaluationsQueueStatus);

        $this->assertProductModelScoreIsComputed(ProductModelId::fromString((string) $pm->getId()));
    }
}

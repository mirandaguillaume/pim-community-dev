<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * @author    Florian Klein (florian.klein@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveProductModelIntegration extends TestCase
{
    use AssertEventCountTrait;

    /**
     * Deleting a root product model removes its sub product model and its variant product through the
     * database cascade (ProductModel::$parent and AbstractProduct::$parent both map ON DELETE CASCADE),
     * not through the removers. BaseRemover::remove() therefore dispatches POST_REMOVE for the root
     * only: exactly 1 product_model.removed business event and no product.removed event.
     *
     * The count of 1 is also the positive control for the 0. Both removed-event subscribers return early
     * without a security user, so it proves the TraceableMessageBus observer and the system user are wired.
     *
     * @test
     */
    public function removing_a_product_model_with_children_raises_only_one_product_model_removed_event()
    {
        $this->arrange();
        // arrange() saves the tree, which dispatches "created" business events: start from an empty log.
        $this->clearMessageBusObserver();

        $rootProductModel = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('root_product_model_two_level');
        // The same remover service the UI delete reaches:
        // ProductModelController::removeAction -> RemoveProductModelHandler -> pim_catalog.remover.product_model.
        $this->get('pim_catalog.remover.product_model')->remove($rootProductModel);

        $this->assertNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('variant_product_1'));
        $this->assertEventCount(1, ProductModelRemoved::class);
        $this->assertEventCount(0, ProductRemoved::class);
    }

    /**
     * @test
     */
    public function removing_a_product_model_deletes_its_children_too()
    {
        $this->arrange();

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productModelRemover = $this->get('pim_catalog.remover.product_model');
        $productModelRepository = $this->get('pim_catalog.repository.product_model');
        $productRepository = $this->get('pim_catalog.repository.product');

        $this->assertTrue($this->productIdentifierIsInIndex('root_product_model_two_level'));
        $this->assertTrue($this->productIdentifierIsInIndex('sub_product_model'));
        $this->assertTrue($this->productIdentifierIsInIndex('variant_product_1'));

        $rootProductModel = $productModelRepository->findOneByIdentifier('root_product_model_two_level');
        $productModelRemover->remove($rootProductModel);

        $this->assertNull($productModelRepository->findOneByIdentifier('root_product_model_two_level'));
        $this->assertNull($productModelRepository->findOneByIdentifier('sub_product_model'));
        $this->assertNull($productRepository->findOneByIdentifier('variant_product_1'));

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->assertFalse($this->productIdentifierIsInIndex('root_product_model_two_level'));
        $this->assertFalse($this->productIdentifierIsInIndex('sub_product_model'));
        $this->assertFalse($this->productIdentifierIsInIndex('variant_product_1'));
    }

    /**
     * Inserts and returns a product model hierarchy of 1 root, 1 sub-model and 1 variant
     */
    private function arrange(): array
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $entityBuilder->createFamilyVariant(
            [
                'code' => 'two_level_family_variant',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_text'],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['a_yes_no'],
                        'attributes' => ['sku', 'a_localized_and_scopable_text_area'],
                    ],
                ],
            ]
        );

        $rootProductModel = $entityBuilder->createProductModel(
            'root_product_model_two_level',
            'two_level_family_variant',
            null,
            []
        );

        $subProductModel = $entityBuilder->createProductModel(
            'sub_product_model',
            'two_level_family_variant',
            $rootProductModel,
            []
        );

        $variant = $entityBuilder->createVariantProduct(
            'variant_product_1',
            'familyA3',
            'two_level_family_variant',
            $subProductModel,
            []
        );

        return [$rootProductModel, $subProductModel, $variant];
    }

    private function productIdentifierIsInIndex(string $identifier): bool
    {
        $res = $this->get('akeneo_elasticsearch.client.product_and_product_model')->search(
            ['query' => ['term' => ['identifier' => $identifier]]]
        );

        return $res['hits']['total']['value'] > 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}

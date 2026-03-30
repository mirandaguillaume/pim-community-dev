<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductModelNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\Context;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCreatedAndUpdatedEventDataBuilderTest extends TestCase
{
    private ProductModelCreatedAndUpdatedEventDataBuilder $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelCreatedAndUpdatedEventDataBuilder();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductModelCreatedAndUpdatedEventDataBuilder::class, $this->sut);
        $this->assertInstanceOf(EventDataBuilderInterface::class, $this->sut);
    }

    public function test_it_supports_a_bulk_event_of_product_model_created_and_updated_events(): void
    {
        $bulkEvent = new BulkEvent([
                    new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '1']),
                    new ProductModelUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '2']),
                ]);
        $this->assertSame(true, $this->sut->supports($bulkEvent));
    }

    public function test_it_does_not_support_a_bulk_event_of_unsupported_product_model_events(): void
    {
        $bulkEvent = new BulkEvent([
                    new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '1']),
                    new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                        'code' => '1',
                        'category_codes' => [],
                    ]),
                ]);
        $this->assertSame(false, $this->sut->supports($bulkEvent));
    }

    public function test_it_builds_a_bulk_event_of_product_created_and_updated_event(): void
    {
        $getConnectorProductModelsQuery = $this->createMock(GetConnectorProductModels::class);

        $context = new Context('ecommerce_0000', 10);
        $jeanEvent = new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
                    'code' => 'jean',
                ]);
        $shoesEvent = new ProductModelUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
                    'code' => 'shoes',
                ]);
        $bulkEvent = new BulkEvent([$jeanEvent, $shoesEvent]);
        $productModelList = new ConnectorProductModelList(2, [
                    $this->buildConnectorProductModel(1, 'jean'),
                    $this->buildConnectorProductModel(2, 'shoes'),
                ]);
        $getConnectorProductModelsQuery->method('fromProductModelCodes')->with(['jean', 'shoes'], 10, null, null, null)->willReturn($productModelList);
        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($jeanEvent, [
                    'resource' => [
                        'code' => 'jean',
                        'family' => 'another_family',
                        'family_variant' => 'another_family_variant',
                        'parent' => null,
                        'categories' => [],
                        'values' => (object)[],
                        'created' => '2020-04-23T15:55:50+00:00',
                        'updated' => '2020-04-25T15:55:50+00:00',
                        'associations' => (object)[],
                        'quantified_associations' => (object)[],
                    ],
                ]);
        $expectedCollection->setEventData($shoesEvent, [
                    'resource' => [
                        'code' => 'shoes',
                        'family' => 'another_family',
                        'family_variant' => 'another_family_variant',
                        'parent' => null,
                        'categories' => [],
                        'values' => (object)[],
                        'created' => '2020-04-23T15:55:50+00:00',
                        'updated' => '2020-04-25T15:55:50+00:00',
                        'associations' => (object)[],
                        'quantified_associations' => (object)[],
                    ],
                ]);
        $collection = $this->build($bulkEvent, $context);
        Assert::assertEquals($expectedCollection, $collection);
    }

    public function test_it_builds_a_bulk_event_of_product_created_and_updated_event_if_a_product_as_been_removed(): void
    {
        $getConnectorProductModelsQuery = $this->createMock(GetConnectorProductModels::class);

        $context = new Context('ecommerce_0000', 10);
        $productList = new ConnectorProductModelList(1, [$this->buildConnectorProductModel(1, 'jean')]);
        $getConnectorProductModelsQuery->method('fromProductModelCodes')->with(['jean', 'shoes'], 10, null, null, null)->willReturn($productList);
        $jeanEvent = new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
                    'code' => 'jean',
                ]);
        $shoesEvent = new ProductModelUpdated(Author::fromNameAndType('julia', Author::TYPE_UI), [
                    'code' => 'shoes',
                ]);
        $bulkEvent = new BulkEvent([$jeanEvent, $shoesEvent]);
        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($jeanEvent, [
                    'resource' => [
                        'code' => 'jean',
                        'family' => 'another_family',
                        'family_variant' => 'another_family_variant',
                        'parent' => null,
                        'categories' => [],
                        'values' => (object)[],
                        'created' => '2020-04-23T15:55:50+00:00',
                        'updated' => '2020-04-25T15:55:50+00:00',
                        'associations' => (object)[],
                        'quantified_associations' => (object)[],
                    ],
                ]);
        $expectedCollection->setEventDataError($shoesEvent, new ProductModelNotFoundException('shoes'));
        $collection = $this->build($bulkEvent, $context);
        Assert::assertEquals($expectedCollection, $collection);
    }

    private function buildConnectorProductModel(int $id, string $code)
    {
            return new ConnectorProductModel(
                $id,
                $code,
                new \DateTimeImmutable('2020-04-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2020-04-25 15:55:50', new \DateTimeZone('UTC')),
                null,
                'another_family',
                'another_family_variant',
                [],
                [],
                [],
                [],
                new ReadValueCollection(),
                null
            );
        }
}

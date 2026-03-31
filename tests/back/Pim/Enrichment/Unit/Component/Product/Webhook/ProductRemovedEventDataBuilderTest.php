<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductRemovedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\Context;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ProductRemovedEventDataBuilderTest extends TestCase
{
    private ProductRemovedEventDataBuilder $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductRemovedEventDataBuilder();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductRemovedEventDataBuilder::class, $this->sut);
        $this->assertInstanceOf(EventDataBuilderInterface::class, $this->sut);
    }

    public function test_it_supports_a_bulk_event_of_product_removed_events(): void
    {
        $bulkEvent = new BulkEvent([
                    new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                        'identifier' => '1',
                        'uuid' => Uuid::uuid4(),
                        'category_codes' => [],
                    ]),
                    new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                        'identifier' => '2',
                        'uuid' => Uuid::uuid4(),
                        'category_codes' => [],
                    ]),
                ]);
        $this->assertSame(true, $this->sut->supports($bulkEvent));
    }

    public function test_it_does_not_support_a_bulk_event_of_unsupported_product_events(): void
    {
        $bulkEvent = new BulkEvent([
                    new ProductCreated(Author::fromNameAndType('julia', Author::TYPE_UI), [
                        'identifier' => '1',
                        'uuid' => Uuid::uuid4(),
                    ]),
                    new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                        'identifier' => '1',
                        'uuid' => Uuid::uuid4(),
                        'category_codes' => [],
                    ]),
                ]);
        $this->assertSame(false, $this->sut->supports($bulkEvent));
    }

    public function test_it_builds_a_bulk_event_of_product_removed_event(): void
    {
        $context = new Context('ecommerce_0000', 10);
        $uuidBlueJean = Uuid::uuid4();
        $blueJeanEvent = new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                    'identifier' => 'blue_jean',
                    'uuid' => $uuidBlueJean,
                    'category_codes' => [],
                ]);
        $uuidRedJean = Uuid::uuid4();
        $redJeanEvent = new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                    'identifier' => 'red_jean',
                    'uuid' => $uuidRedJean,
                    'category_codes' => [],
                ]);
        $bulkEvent = new BulkEvent([$blueJeanEvent, $redJeanEvent]);
        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($blueJeanEvent, ['resource' => [
                    'identifier' => 'blue_jean',
                    'uuid' => $uuidBlueJean
                ]]);
        $expectedCollection->setEventData($redJeanEvent, ['resource' => [
                    'identifier' => 'red_jean',
                    'uuid' => $uuidRedJean
                ]]);
        $collection = $this->sut->build($bulkEvent, $context);
        Assert::assertEquals($expectedCollection, $collection);
    }
}

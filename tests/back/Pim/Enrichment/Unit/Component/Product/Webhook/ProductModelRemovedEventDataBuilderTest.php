<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelRemovedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\Webhook\Context;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Platform\Component\Webhook\EventDataCollection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ProductModelRemovedEventDataBuilderTest extends TestCase
{
    private ProductModelRemovedEventDataBuilder $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelRemovedEventDataBuilder();
        $this->sut->beConstructedWith();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductModelRemovedEventDataBuilder::class, $this->sut);
        $this->assertInstanceOf(EventDataBuilderInterface::class, $this->sut);
    }

    public function test_it_supports_a_bulk_event_of_product_removed_events(): void
    {
        $bulkEvent = new BulkEvent([
                    new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '1', 'category_codes' => []]),
                    new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '2', 'category_codes' => []]),
                ]);
        $this->assertSame(true, $this->sut->supports($bulkEvent));
    }

    public function test_it_does_not_support_a_bulk_event_of_unsupported_product_events(): void
    {
        $bulkEvent = new BulkEvent([
                    new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), ['code' => '1', 'category_codes' => []]),
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
        $blueJeanEvent = new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                    'code' => 'blue_jean',
                    'category_codes' => [],
                ]);
        $redJeanEvent = new ProductModelRemoved(Author::fromNameAndType('julia', Author::TYPE_UI), [
                    'code' => 'red_jean',
                    'category_codes' => [],
                ]);
        $bulkEvent = new BulkEvent([$blueJeanEvent, $redJeanEvent]);
        $expectedCollection = new EventDataCollection();
        $expectedCollection->setEventData($blueJeanEvent, ['resource' => ['code' => 'blue_jean']]);
        $expectedCollection->setEventData($redJeanEvent, ['resource' => ['code' => 'red_jean']]);
        $collection = $this->build($bulkEvent, $context);
        Assert::assertEquals($expectedCollection, $collection);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer;

use Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer\ProductModelEventNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Platform\Component\EventQueue\Author;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelEventNormalizerTest extends TestCase
{
    private ProductModelEventNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelEventNormalizer();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductModelEventNormalizer::class, $this->sut);
    }

    public function test_it_supports_a_product_model_created_event(): void
    {
        $event = $this->createMock(ProductModelCreated::class);

        $this->assertSame(true, $this->sut->supports($event));
    }

    public function test_it_supports_a_product_model_updated_event(): void
    {
        $event = $this->createMock(ProductModelUpdated::class);

        $this->assertSame(true, $this->sut->supports($event));
    }

    public function test_it_supports_a_product_model_removed_event(): void
    {
        $event = $this->createMock(ProductModelRemoved::class);

        $this->assertSame(true, $this->sut->supports($event));
    }

    public function test_it_normalizes_a_product_model_created_event(): void
    {
        $event = new ProductModelCreated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['code' => 'sneakers'],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );
        $this->assertSame([
                    'action' => 'product_model.created',
                    'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                    'event_datetime' => '1970-01-01T00:00:00+00:00',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'product_model_code' => 'sneakers',
                ], $this->sut->normalize($event));
    }

    public function test_it_normalizes_a_product_model_updated_event(): void
    {
        $event = new ProductModelUpdated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['code' => 'sneakers'],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );
        $this->assertSame([
                    'action' => 'product_model.updated',
                    'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                    'event_datetime' => '1970-01-01T00:00:00+00:00',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'product_model_code' => 'sneakers',
                ], $this->sut->normalize($event));
    }

    public function test_it_normalizes_a_product_model_removed_event(): void
    {
        $event = new ProductModelRemoved(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            ['code' => 'sneakers', 'category_codes' => []],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );
        $this->assertSame([
                    'action' => 'product_model.removed',
                    'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                    'event_datetime' => '1970-01-01T00:00:00+00:00',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'product_model_code' => 'sneakers',
                ], $this->sut->normalize($event));
    }
}

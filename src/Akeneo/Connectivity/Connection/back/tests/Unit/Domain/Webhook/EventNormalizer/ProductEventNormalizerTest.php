<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Webhook\EventNormalizer;

use Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer\ProductEventNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Platform\Component\EventQueue\Author;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEventNormalizerTest extends TestCase
{
    private ProductEventNormalizer $sut;

    protected function setUp(): void
    {
        date_default_timezone_set('UTC');
        $this->sut = new ProductEventNormalizer();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductEventNormalizer::class, $this->sut);
    }

    public function test_it_supports_a_product_created_event(): void
    {
        $event = $this->createMock(ProductCreated::class);

        $this->assertSame(true, $this->sut->supports($event));
    }

    public function test_it_supports_a_product_updated_event(): void
    {
        $event = $this->createMock(ProductUpdated::class);

        $this->assertSame(true, $this->sut->supports($event));
    }

    public function test_it_supports_a_product_removed_event(): void
    {
        $event = $this->createMock(ProductRemoved::class);

        $this->assertSame(true, $this->sut->supports($event));
    }

    public function test_it_normalizes_a_product_created_event(): void
    {
        $uuid = Uuid::uuid4();
        $event = new ProductCreated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [
                        'identifier' => 'blue_sneakers',
                        'uuid' => $uuid,
                    ],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );
        $this->assertSame([
                    'action' => 'product.created',
                    'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                    'event_datetime' => '1970-01-01T00:00:00+00:00',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'product_identifier' => 'blue_sneakers',
                    'product_uuid' => $uuid->toString(),
                ], $this->sut->normalize($event));
    }

    public function test_it_normalizes_a_product_updated_event(): void
    {
        $uuid = Uuid::uuid4();
        $event = new ProductUpdated(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [
                        'identifier' => 'blue_sneakers',
                        'uuid' => $uuid,
                    ],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );
        $this->assertSame([
                    'action' => 'product.updated',
                    'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                    'event_datetime' => '1970-01-01T00:00:00+00:00',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'product_identifier' => 'blue_sneakers',
                    'product_uuid' => $uuid->toString(),
                ], $this->sut->normalize($event));
    }

    public function test_it_normalizes_a_product_removed_event(): void
    {
        $uuid = Uuid::uuid4();
        $event = new ProductRemoved(
            Author::fromNameAndType('julia', Author::TYPE_UI),
            [
                        'identifier' => 'blue_sneakers',
                        'uuid' => $uuid,
                        'category_codes' => [],
                    ],
            0,
            '9979c367-595d-42ad-9070-05f62f31f49b'
        );
        $this->assertSame([
                    'action' => 'product.removed',
                    'event_id' => '9979c367-595d-42ad-9070-05f62f31f49b',
                    'event_datetime' => '1970-01-01T00:00:00+00:00',
                    'author' => 'julia',
                    'author_type' => 'ui',
                    'product_identifier' => 'blue_sneakers',
                    'product_uuid' => $uuid->toString(),
                ], $this->sut->normalize($event));
    }
}

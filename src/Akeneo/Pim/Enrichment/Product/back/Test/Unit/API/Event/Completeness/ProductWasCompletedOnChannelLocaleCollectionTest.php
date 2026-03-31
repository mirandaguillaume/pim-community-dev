<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Event\Completeness;

use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocale;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocaleCollection;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWasCompletedOnChannelLocaleCollectionTest extends TestCase
{
    private ProductWasCompletedOnChannelLocaleCollection $sut;

    protected function setUp(): void
    {
    }

    public function test_it_cant_be_created_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ProductWasCompletedOnChannelLocaleCollection([]);
    }

    public function test_it_cant_be_created_with_invalid_products_completeness(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ProductWasCompletedOnChannelLocaleCollection(['completeness1','completeness2']);
    }

    public function test_it_returns_the_events(): void
    {
        $events = [
                    new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid(Uuid::uuid4()), new \DateTimeImmutable(), 'ecormmerce', 'en_US', '1'),
                    new ProductWasCompletedOnChannelLocale(ProductUuid::fromUuid(Uuid::uuid4()), new \DateTimeImmutable(), 'ecormmerce', 'fr_FR', null),
                ];
        $this->sut = new ProductWasCompletedOnChannelLocaleCollection($events);
        $this->assertSame($events, $this->sut->all());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Message;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use PHPUnit\Framework\TestCase;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCreatedTest extends TestCase
{
    private ProductModelCreated $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI),
            ['code' => 'product_model_code'],
            1598968800,
            '523e4557-e89b-12d3-a456-426614174000',);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductModelCreated::class, $this->sut);
    }

    public function test_it_is_an_event(): void
    {
        $this->assertInstanceOf(Event::class, $this->sut);
    }

    public function test_it_validates_the_product_model_code(): void
    {
        $this->expectException(new \InvalidArgumentException('Expected the key "code" to exist.'));
        new ProductModelCreated(Author::fromNameAndType('julia', Author::TYPE_UI),
                    [],
                    1598968800,
                    '523e4557-e89b-12d3-a456-426614174000',);
    }

    public function test_it_returns_the_name(): void
    {
        $this->assertSame('product_model.created', $this->sut->getName());
    }

    public function test_it_returns_the_author(): void
    {
        $this->assertEquals(Author::fromNameAndType('julia', Author::TYPE_UI), $this->sut->getAuthor());
    }

    public function test_it_returns_the_data(): void
    {
        $this->assertSame(['code' => 'product_model_code'], $this->sut->getData());
    }

    public function test_it_returns_the_timestamp(): void
    {
        $this->assertSame(1598968800, $this->sut->getTimestamp());
    }

    public function test_it_returns_the_uuid(): void
    {
        $this->assertSame('523e4557-e89b-12d3-a456-426614174000', $this->sut->getUuid());
    }

    public function test_it_returns_the_product_model_code(): void
    {
        $this->assertSame('product_model_code', $this->sut->getCode());
    }
}

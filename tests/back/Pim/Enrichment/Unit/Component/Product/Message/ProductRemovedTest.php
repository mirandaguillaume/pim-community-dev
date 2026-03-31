<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Message;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductRemovedTest extends TestCase
{
    private ProductRemoved $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI),
            [
                'identifier' => 'product_identifier',
                'uuid' => Uuid::fromString('5dd9eb8b-261f-4e76-bf1d-f407063f931d'),
                'category_codes' => ['category_code_1', 'category_code_2'],
            ],
            1598968800,
            '523e4557-e89b-12d3-a456-426614174000',);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductRemoved::class, $this->sut);
    }

    public function test_it_is_an_event(): void
    {
        $this->assertInstanceOf(Event::class, $this->sut);
    }

    public function test_it_validates_the_product_identifier(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('Expected the key "identifier" to exist.');
        new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI),
                    [],
                    1598968800,
                    '523e4557-e89b-12d3-a456-426614174000',);
    }

    public function test_it_validates_the_product_uuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('Expected the key "uuid" to exist.');
        new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI),
                    [
                        'identifier' => 'product_identifier',
                        'category_codes' => ['category_code_1', 'category_code_2'],
                    ],
                    1598968800,
                    '523e4557-e89b-12d3-a456-426614174000',);
    }

    public function test_it_validates_the_category_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('Expected the key "category_codes" to exist.');
        new ProductRemoved(Author::fromNameAndType('julia', Author::TYPE_UI),
                    [
                        'identifier' => 'product_identifier',
                        'uuid' => Uuid::uuid4(),
                    ],
                    1598968800,
                    '523e4557-e89b-12d3-a456-426614174000',);
    }

    public function test_it_returns_the_name(): void
    {
        $this->assertSame('product.removed', $this->sut->getName());
    }

    public function test_it_returns_the_author(): void
    {
        $this->assertEquals(Author::fromNameAndType('julia', Author::TYPE_UI), $this->sut->getAuthor());
    }

    public function test_it_returns_the_data(): void
    {
        $this->assertEquals([
                    'identifier' => 'product_identifier',
                    'uuid' => Uuid::fromString('5dd9eb8b-261f-4e76-bf1d-f407063f931d'),
                    'category_codes' => ['category_code_1', 'category_code_2'],
                ], $this->sut->getData());
    }

    public function test_it_returns_the_timestamp(): void
    {
        $this->assertSame(1598968800, $this->sut->getTimestamp());
    }

    public function test_it_returns_the_uuid(): void
    {
        $this->assertSame('523e4557-e89b-12d3-a456-426614174000', $this->sut->getUuid());
    }

    public function test_it_returns_the_product_uuid(): void
    {
        $this->assertEquals(Uuid::fromString('5dd9eb8b-261f-4e76-bf1d-f407063f931d'), $this->sut->getProductUuid());
    }

    public function test_it_returns_the_product_identifier(): void
    {
        $this->assertSame('product_identifier', $this->sut->getIdentifier());
    }

    public function test_it_returns_the_category_codes(): void
    {
        $this->assertSame(['category_code_1', 'category_code_2'], $this->sut->getCategoryCodes());
    }
}

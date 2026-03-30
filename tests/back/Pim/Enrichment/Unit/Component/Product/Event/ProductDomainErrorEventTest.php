<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Event;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductDomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductDomainErrorEventTest extends TestCase
{
    private DomainErrorInterface|MockObject $error;
    private ProductInterface|MockObject $product;
    private ProductDomainErrorEvent $sut;

    protected function setUp(): void
    {
        $this->error = $this->createMock(DomainErrorInterface::class);
        $this->product = $this->createMock(ProductInterface::class);
        $this->sut = new ProductDomainErrorEvent($this->error, $this->product);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductDomainErrorEvent::class, $this->sut);
    }

    public function test_it_returns_the_error(): void
    {
        $this->assertSame($this->error, $this->sut->getError());
    }

    public function test_it_returns_the_product(): void
    {
        $this->assertSame($this->product, $this->sut->getProduct());
    }

    public function test_it_works_without_product(): void
    {
        $this->sut = new ProductDomainErrorEvent($this->error, null);
        $this->assertNull($this->sut->getProduct());
    }
}

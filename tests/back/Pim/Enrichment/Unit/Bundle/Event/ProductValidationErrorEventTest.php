<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Event;

use Akeneo\Pim\Enrichment\Bundle\Event\ProductValidationErrorEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ProductValidationErrorEventTest extends TestCase
{
    private ConstraintViolationListInterface|MockObject $constraintViolationList;
    private ProductInterface|MockObject $product;
    private ProductValidationErrorEvent $sut;

    protected function setUp(): void
    {
        $this->constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $this->product = $this->createMock(ProductInterface::class);
        $this->sut = new ProductValidationErrorEvent($this->constraintViolationList, $this->product);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductValidationErrorEvent::class, $this->sut);
    }

    public function test_it_returns_the_constraint_violation_list(): void
    {
        $this->assertSame($this->constraintViolationList, $this->sut->getConstraintViolationList());
    }

    public function test_it_returns_the_product(): void
    {
        $this->assertSame($this->product, $this->sut->getProduct());
    }
}

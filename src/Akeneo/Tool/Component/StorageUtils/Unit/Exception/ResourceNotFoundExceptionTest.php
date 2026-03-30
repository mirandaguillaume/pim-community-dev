<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\StorageUtils\Exception\ResourceNotFoundException;
use PHPUnit\Framework\TestCase;

class ResourceNotFoundExceptionTest extends TestCase
{
    private ResourceNotFoundException $sut;

    protected function setUp(): void
    {
        $this->sut = new ResourceNotFoundException($objectClassName);
        $objectClassName = Product::class;
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ResourceNotFoundException::class, $this->sut);
    }

    public function test_it_is_an_exception(): void
    {
        $this->assertInstanceOf(\Exception::class, $this->sut);
    }

    public function test_it_returns_an_exception_message(): void
    {
        $this->assertSame(sprintf("Can't find resource of type %s", Product::class), $this->sut->getMessage());
    }
}

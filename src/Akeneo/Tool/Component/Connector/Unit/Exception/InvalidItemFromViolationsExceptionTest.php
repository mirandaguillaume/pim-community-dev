<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class InvalidItemFromViolationsExceptionTest extends TestCase
{
    private InvalidItemFromViolationsException $sut;

    protected function setUp(): void
    {
    }

    public function test_it_formats_a_violation_message_for_an_invalid_scalar(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $violation->method('getInvalidValue')->willReturn('my bad value');
        $violation->method('getPropertyPath')->willReturn('foo.bar.baz');
        $violation->method('getMessage')->willReturn('invalid value');
        $this->sut = new InvalidItemFromViolationsException(
            new ConstraintViolationList([$violation]),
            new DataInvalidItem(['foo' => 'bar'])
        );
        $this->assertSame('foo.bar.baz: invalid value: my bad value' . PHP_EOL, $this->sut->getMessage());
    }

    public function test_it_formats_violation_message_for_an_invalid_stringifiable_object(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $class = new class {
            public function __toString()
            {
                return 'my object';
            }
        }
        ;
        $violation->method('getInvalidValue')->willReturn(new $class());
        $violation->method('getPropertyPath')->willReturn('foo.bar.baz');
        $violation->method('getMessage')->willReturn('invalid value');
        $this->sut = new InvalidItemFromViolationsException(
            new ConstraintViolationList([$violation]),
            new DataInvalidItem(['foo' => 'bar'])
        );
        $this->assertSame('foo.bar.baz: invalid value: my object' . PHP_EOL, $this->sut->getMessage());
    }

    public function test_it_formats_violation_message_for_an_invalid_non_stringifiable_object(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $violation->method('getInvalidValue')->willReturn(new \stdClass());
        $violation->method('getPropertyPath')->willReturn('foo.bar.baz');
        $violation->method('getMessage')->willReturn('Unexpected value');
        $this->sut = new InvalidItemFromViolationsException(
            new ConstraintViolationList([$violation]),
            new DataInvalidItem(['foo' => 'bar'])
        );
        $this->assertSame('foo.bar.baz: Unexpected value' . PHP_EOL, $this->sut->getMessage());
    }

    public function test_it_formats_violation_message_for_an_invalid_product_price(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $violation->method('getInvalidValue')->willReturn(new ProductPrice(3299.99, 'EUR'));
        $violation->method('getPropertyPath')->willReturn('foo.bar.baz');
        $violation->method('getMessage')->willReturn('This value should be lower than 3000');
        $this->sut = new InvalidItemFromViolationsException(
            new ConstraintViolationList([$violation]),
            new DataInvalidItem(['foo' => 'bar'])
        );
        $this->assertSame('foo.bar.baz: This value should be lower than 3000: 3299.99 EUR' . PHP_EOL, $this->sut->getMessage());
    }

    public function test_it_formats_violation_message_for_an_invalid_array_of_strings(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $violation->method('getInvalidValue')->willReturn(['foo', 'bar', 'baz']);
        $violation->method('getPropertyPath')->willReturn('foo.bar.baz');
        $violation->method('getMessage')->willReturn('unknown codes');
        $this->sut = new InvalidItemFromViolationsException(
            new ConstraintViolationList([$violation]),
            new DataInvalidItem(['foo' => 'bar'])
        );
        $this->assertSame('foo.bar.baz: unknown codes: [foo, bar, baz]' . PHP_EOL, $this->sut->getMessage());
    }

    public function test_it_formats_violation_message_for_an_invalid_array_of_objects(): void
    {
        $violation = $this->createMock(ConstraintViolationInterface::class);

        $class = new class {
            public function __construct(private readonly string $data = 'foo')
            {
            }
            public function __toString()
            {
                return $this->data;
            }
        }
        ;
        $violation->method('getInvalidValue')->willReturn([new $class(), new $class('bar')]);
        $violation->method('getPropertyPath')->willReturn('foo.bar.baz');
        $violation->method('getMessage')->willReturn('unknown codes');
        $this->sut = new InvalidItemFromViolationsException(
            new ConstraintViolationList([$violation]),
            new DataInvalidItem(['foo' => 'bar'])
        );
        $this->assertSame('foo.bar.baz: unknown codes: [foo, bar]' . PHP_EOL, $this->sut->getMessage());
    }

    public function test_it_formats_multiple_violations(): void
    {
        $firstViolation = $this->createMock(ConstraintViolationInterface::class);
        $secondViolation = $this->createMock(ConstraintViolationInterface::class);
        $thirdViolation = $this->createMock(ConstraintViolationInterface::class);

        $firstViolation->method('getInvalidValue')->willReturn(['foo', 'bar']);
        $firstViolation->method('getPropertyPath')->willReturn('values.conditions-<all_channels>');
        $firstViolation->method('getMessage')->willReturn('Unknown conditions');
        $secondViolation->method('getInvalidValue')->willReturn(new ProductPrice(20.5646, 'USD'));
        $secondViolation->method('getPropertyPath')->willReturn('values.price-<all_locales>');
        $secondViolation->method('getMessage')->willReturn('Invalid price data');
        $thirdViolation->method('getInvalidValue')->willReturn([new \stdClass()]);
        $thirdViolation->method('getPropertyPath')->willReturn('values.collection-<all_channels>-<all_locales>');
        $thirdViolation->method('getMessage')->willReturn('This collection should contain at least 2 elements');
        $this->sut = new InvalidItemFromViolationsException(
            new ConstraintViolationList([
                        $firstViolation,
                        $secondViolation,
                        $thirdViolation,
                    ]),
            new DataInvalidItem(['foo' => 'bar'])
        );
        $this->assertSame(<<<EOL
                        values.conditions: Unknown conditions: [foo, bar]

                        values.price: Invalid price data: 20.5646 USD

                        values.collection: This collection should contain at least 2 elements

                        EOL, $this->sut->getMessage());
    }
}

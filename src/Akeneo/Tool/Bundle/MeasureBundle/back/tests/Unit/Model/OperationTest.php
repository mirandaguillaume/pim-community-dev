<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{
    private Operation $sut;

    protected function setUp(): void
    {
        $this->sut = Operation::create(self::OPERATOR, self::VALUE);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Operation::class, $this->sut);
    }

    public function test_it_should_be_normalizable(): void
    {
        $this->assertSame(['operator' => self::OPERATOR, 'value' => self::VALUE], $this->sut->normalize());
    }

    public function test_it_cannot_be_constructed_with_an_unsupported_operator(): void
    {
        $invalidOperation = 'invalid_operation';
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create($invalidOperation, self::VALUE);
    }

    public function test_it_cannot_be_constructed_with_a_non_numeric_string_value(): void
    {
        $invalidValue = 'not a numeric_value';
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create(self::OPERATOR, $invalidValue);
    }

    public function test_it_cannot_be_constructed_with_scientific_notation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create(self::OPERATOR, '7E-10');
    }
}

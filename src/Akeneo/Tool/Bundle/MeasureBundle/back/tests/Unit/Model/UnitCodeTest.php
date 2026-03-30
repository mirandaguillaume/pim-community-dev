<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use PHPUnit\Framework\TestCase;

class UnitCodeTest extends TestCase
{
    private UnitCode $sut;

    protected function setUp(): void
    {
        $this->sut = UnitCode::fromString(self::UNIT_CODE);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UnitCode::class, $this->sut);
    }

    public function test_it_is_normalizable(): void
    {
        $this->assertSame(self::UNIT_CODE, $this->sut->normalize());
    }

    public function test_it_is_comparable(): void
    {
        $this->assertSame(true, $this->sut->equals(UnitCode::fromString(self::UNIT_CODE)));
        $this->assertSame(false, $this->sut->equals(UnitCode::fromString('centimeter')));
    }

    public function test_it_cannot_be_constructed_with_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->fromString('');
    }

    public function test_it_should_contain_only_letters_numbers_and_underscores(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UnitCode::fromString('badId!');
    }

    public function test_it_cannot_be_constructed_with_a_string_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->fromString(str_repeat('a', 256));
    }
}

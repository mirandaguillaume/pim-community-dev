<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use PHPUnit\Framework\TestCase;

class MeasurementFamilyCodeTest extends TestCase
{
    private const MEASUREMENT_FAMILY_CODE = 'Length';

    private MeasurementFamilyCode $sut;

    protected function setUp(): void
    {
        $this->sut = MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MeasurementFamilyCode::class, $this->sut);
    }

    public function test_it_is_normalizable(): void
    {
        $this->assertSame(self::MEASUREMENT_FAMILY_CODE, $this->sut->normalize());
    }

    public function test_it_cannot_be_constructed_with_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->fromString('');
    }

    public function test_it_should_contain_only_letters_numbers_and_underscores(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MeasurementFamilyCode::fromString('badId!');
    }

    public function test_it_cannot_be_constructed_with_a_string_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->fromString(str_repeat('a', 256));
    }
}

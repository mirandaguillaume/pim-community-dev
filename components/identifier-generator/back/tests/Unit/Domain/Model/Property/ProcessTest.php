<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProcessTest extends TestCase
{
    private Process $sut;

    protected function setUp(): void
    {
        $this->sut = Process::fromNormalized([
            'type' => 'truncate',
            'operator' => '=',
            'value' => 3,
        ], );
    }

    public function test_it_is_a_family_generation_process(): void
    {
        $this->assertInstanceOf(Process::class, $this->sut);
    }

    public function test_it_returns_a_type(): void
    {
        $this->assertSame('truncate', $this->sut->type());
    }

    public function test_it_normalize_a_process(): void
    {
        $this->assertSame([
            'type' => 'truncate',
            'operator' => '=',
            'value' => 3,
        ], $this->sut->normalize());
    }

    public function test_it_should_throw_an_exception_when_no_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Process::fromNormalized([]);
    }

    public function test_it_should_throw_an_exception_when_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Process::fromNormalized(['type' => 'unknown']);
    }

    public function test_it_should_not_throw_an_exception_when_type_no_is_well_formed(): void
    {
        Process::fromNormalized(['type' => 'no']);
        $this->addToAssertionCount(1);
    }

    public function test_it_should_throw_an_exception_when_type_truncate_and_no_operator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Process::fromNormalized(['type' => 'truncate', 'value' => 3]);
    }

    public function test_it_should_throw_an_exception_when_type_truncate_and_empty_operator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Process::fromNormalized(['type' => 'truncate', 'operator' => null, 'value' => 3]);
    }

    public function test_it_should_throw_an_exception_when_type_truncate_and_unknown_operator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Process::fromNormalized(['type' => 'truncate', 'operator' => 'unknown', 'value' => 3]);
    }

    public function test_it_should_throw_an_exception_when_type_truncate_and_no_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Process::fromNormalized(['type' => 'truncate', 'operator' => '=']);
    }

    public function test_it_should_throw_an_exception_when_type_truncate_and_not_numeric_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Process::fromNormalized(['type' => 'truncate', 'operator' => '=', 'value' => 'bar']);
    }

    public function test_it_should_throw_an_exception_when_type_truncate_and_too_high_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Process::fromNormalized(['type' => 'truncate', 'operator' => '=', 'value' => 6]);
    }

    public function test_it_should_throw_an_exception_when_type_truncate_and_too_low_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Process::fromNormalized(['type' => 'truncate', 'operator' => '=', 'value' => 0]);
    }

    public function test_it_should_not_throw_an_exception_when_type_nomenclature_is_well_formed(): void
    {
        Process::fromNormalized(['type' => 'nomenclature']);
        $this->addToAssertionCount(1);
    }
}

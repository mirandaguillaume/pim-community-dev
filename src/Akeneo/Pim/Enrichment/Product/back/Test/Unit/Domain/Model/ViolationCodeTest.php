<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\Model;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use PHPUnit\Framework\TestCase;

class ViolationCodeTest extends TestCase
{
    private ViolationCode $sut;

    protected function setUp(): void
    {
        $this->sut = new ViolationCode();
    }

    public function test_it_builds_global_violation_code(): void
    {
        $this->assertSame(3, $this->sut->buildGlobalViolationCode(1, 2));
        $this->assertSame(7, $this->sut->buildGlobalViolationCode(1, 2, 4));
        $this->assertSame(9, $this->sut->buildGlobalViolationCode(1, 8));
    }

    public function test_it_contains_code_into_global_code(): void
    {
        $this->assertSame(true, $this->sut->containsViolationCode(7, 1));
        $this->assertSame(true, $this->sut->containsViolationCode(7, 2));
        $this->assertSame(true, $this->sut->containsViolationCode(7, 4));
        $this->assertSame(false, $this->sut->containsViolationCode(7, 8));
    }
}

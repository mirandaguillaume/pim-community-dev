<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use PHPUnit\Framework\TestCase;

class NomenclatureDefinitionTest extends TestCase
{
    private NomenclatureDefinition $sut;

    protected function setUp(): void
    {
        $this->sut = new NomenclatureDefinition('<=', 3);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(NomenclatureDefinition::class, $this->sut);
    }

    public function test_it_has_an_operator(): void
    {
        $this->assertSame('<=', $this->sut->operator());
    }

    public function test_it_clones_with_operator(): void
    {
        $this->assertEquals(new NomenclatureDefinition('=', 3), $this->sut->withOperator('='));
    }

    public function test_it_has_a_value(): void
    {
        $this->assertSame(3, $this->sut->value());
    }

    public function test_it_clones_with_value(): void
    {
        $this->assertEquals(new NomenclatureDefinition('<=', 5), $this->sut->withValue(5));
    }
}

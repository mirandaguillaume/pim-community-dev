<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository\InMemorySimpleSelectNomenclatureRepository;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemorySimpleSelectNomenclatureRepositoryTest extends TestCase
{
    private InMemorySimpleSelectNomenclatureRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemorySimpleSelectNomenclatureRepository();
    }

    public function test_it_is_a_simple_select_nomenclature_repository(): void
    {
        $this->assertInstanceOf(SimpleSelectNomenclatureRepository::class, $this->sut);
    }

    public function test_it_can_save_simple_select_nomenclatures(): void
    {
        $simpleSelectNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['s' => 'sma', 'm' => 'med']
        );
        $this->sut->update('size', $simpleSelectNomenclature);
        $this->assertEquals([
            'size' => $simpleSelectNomenclature,
        ], $this->sut->nomenclatureDefinitions);
        $anotherSimpleSelectNomenclature = new NomenclatureDefinition(
            '<=',
            5,
            true,
            ['blue' => 'blue', 'red' => 'red']
        );
        $this->sut->update('color', $anotherSimpleSelectNomenclature);
        $this->assertEquals([
            'size' => $simpleSelectNomenclature,
            'color' => $anotherSimpleSelectNomenclature,
        ], $this->sut->nomenclatureDefinitions);
    }

    public function test_it_can_update_simple_select_nomenclatures_while_ignoring_case(): void
    {
        $simpleSelectNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['s' => 'sma', 'm' => 'med']
        );
        $this->sut->update('size', $simpleSelectNomenclature);
        $this->assertEquals([
            'size' => $simpleSelectNomenclature,
        ], $this->sut->nomenclatureDefinitions);
        $simpleSelectNomenclature = new NomenclatureDefinition(
            '=',
            5,
            false,
            ['m' => null, 'l' => 'lrg']
        );
        $this->sut->update('siZE', $simpleSelectNomenclature);
        $this->assertEquals([
            'size' => new NomenclatureDefinition(
                '=',
                5,
                false,
                ['s' => 'sma', 'l' => 'lrg']
            ),
        ], $this->sut->nomenclatureDefinitions);
    }

    public function test_it_can_retrieve_a_nomenclature_with_its_code_while_ignoring_case(): void
    {
        $simpleSelectNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['s' => 'sma', 'm' => 'med']
        );
        $this->sut->update('size', $simpleSelectNomenclature);
        $result = $this->sut->get('size');
        $this->assertEquals($simpleSelectNomenclature, $result);
        $resultWithCase = $this->sut->get('siZE');
        $this->assertEquals($simpleSelectNomenclature, $resultWithCase);
    }
}

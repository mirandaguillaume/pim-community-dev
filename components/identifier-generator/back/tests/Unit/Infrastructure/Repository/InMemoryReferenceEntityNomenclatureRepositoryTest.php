<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository\InMemoryReferenceEntityNomenclatureRepository;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryReferenceEntityNomenclatureRepositoryTest extends TestCase
{
    private InMemoryReferenceEntityNomenclatureRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryReferenceEntityNomenclatureRepository();
    }

    public function test_it_is_a_reference_entity_nomenclature_repository(): void
    {
        $this->assertInstanceOf(ReferenceEntityNomenclatureRepository::class, $this->sut);
    }

    public function test_it_can_save_reference_entity_nomenclatures(): void
    {
        $refEntityNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['akeneo' => 'akn', 'adidas' => 'adds']
        );
        $this->sut->update('brand', $refEntityNomenclature);
        $this->assertEquals([
            'brand' => $refEntityNomenclature,
        ], $this->sut->nomenclatureDefinitions);
        $anotherRefEntityNomenclature = new NomenclatureDefinition(
            '<=',
            5,
            true,
            ['purple' => 'ppl', 'blue' => 'ble']
        );
        $this->sut->update('color', $anotherRefEntityNomenclature);
        $this->assertEquals([
            'brand' => $refEntityNomenclature,
            'color' => $anotherRefEntityNomenclature,
        ], $this->sut->nomenclatureDefinitions);
    }

    public function test_it_can_update_reference_entity_nomenclatures_while_ignoring_case(): void
    {
        $refEntityNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['akeneo' => 'akn', 'adidas' => 'adds']
        );
        $this->sut->update('brand', $refEntityNomenclature);
        $this->assertEquals([
            'brand' => $refEntityNomenclature,
        ], $this->sut->nomenclatureDefinitions);
        $refEntityNomenclature = new NomenclatureDefinition(
            '=',
            5,
            false,
            ['akeneo' => null, 'zara' => 'zra']
        );
        $this->sut->update('braND', $refEntityNomenclature);
        $this->assertEquals([
            'brand' => new NomenclatureDefinition(
                '=',
                5,
                false,
                ['adidas' => 'adds', 'zara' => 'zra']
            ),
        ], $this->sut->nomenclatureDefinitions);
    }

    public function test_it_can_retrieve_a_nomenclature_with_its_code_while_ignoring_case(): void
    {
        $refEntityNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['akeneo' => 'akn', 'adidas' => 'adds']
        );
        $this->sut->update('brand', $refEntityNomenclature);
        $result = $this->sut->get('brand');
        $this->assertEquals($refEntityNomenclature, $result);
        $resultWithCase = $this->sut->get('brAND');
        $this->assertEquals($refEntityNomenclature, $resultWithCase);
    }
}

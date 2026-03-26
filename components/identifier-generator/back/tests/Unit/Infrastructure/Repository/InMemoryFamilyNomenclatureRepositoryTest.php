<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository\InMemoryFamilyNomenclatureRepository;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFamilyNomenclatureRepositoryTest extends TestCase
{
    private InMemoryFamilyNomenclatureRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryFamilyNomenclatureRepository();
    }

    public function test_it_is_a_family_nomenclature_repository(): void
    {
        $this->assertInstanceOf(InMemoryFamilyNomenclatureRepository::class, $this->sut);
    }

    public function test_it_can_save_family_nomenclatures(): void
    {
        $familyNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            ['family1' => 'Foo', 'family2' => 'Bar']
        );
        $this->sut->update($familyNomenclature);
        $result = $this->sut->get();
        $this->assertEquals($familyNomenclature, $result);
    }

    public function test_it_can_update_simple_select_nomenclatures_while_ignoring_case(): void
    {
        $valuesWithKeysUsingCase = ['fAmIlY1' => 'Foo', 'FaMiLy2' => 'Bar'];
        $familyNomenclatureWithCase = new NomenclatureDefinition(
            '=',
            3,
            false,
            $valuesWithKeysUsingCase
        );
        $this->sut->update($familyNomenclatureWithCase);
        $valuesWithKeysInLowerCase = ['family1' => 'Foo', 'family2' => 'Bar'];
        $familyNomenclature = new NomenclatureDefinition(
            '=',
            3,
            false,
            $valuesWithKeysInLowerCase
        );
        $result = $this->sut->get();
        $this->assertEquals($familyNomenclature, $result);
    }
}

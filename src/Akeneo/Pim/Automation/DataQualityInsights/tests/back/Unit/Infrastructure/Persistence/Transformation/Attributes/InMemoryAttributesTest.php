<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes\InMemoryAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAttributesTest extends TestCase
{
    private InMemoryAttributes $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryAttributes([
            'sku' => 1,
            'name' => 2,
            "description" => 42,
        ]);
    }

    public function test_it_gets_attributes_ids_from_codes(): void
    {
        $this->assertSame(['name' => 2, 'description' => 42], $this->sut->getIdsByCodes(['name', 'description']));
    }

    public function test_it_gets_attributes_codes_from_ids(): void
    {
        $this->assertSame([2 => 'name', 42 => 'description'], $this->sut->getCodesByIds([2, 42]));
    }

    public function test_it_ignores_unknown_attributes(): void
    {
        $this->assertSame(['name' => 2, 'description' => 42], $this->sut->getIdsByCodes(['name', 'title','description']));
        $this->assertSame([2 => 'name', 42 => 'description'], $this->sut->getCodesByIds([567, 2, 42]));
    }
}

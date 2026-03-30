<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetCategoriesTest extends TestCase
{
    private SetCategories $sut;

    protected function setUp(): void
    {
        $this->sut = new SetCategories(['categoryA', 'categoryB']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetCategories::class, $this->sut);
        $this->assertInstanceOf(CategoryUserIntent::class, $this->sut);
        $this->assertSame(['categoryA', 'categoryB'], $this->sut->categoryCodes());
    }

    public function test_it_requires_non_empty_values_in_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SetCategories(['']);
    }

    public function test_it_requires_string_values_in_the_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SetCategories(['test', 42]);
    }
}

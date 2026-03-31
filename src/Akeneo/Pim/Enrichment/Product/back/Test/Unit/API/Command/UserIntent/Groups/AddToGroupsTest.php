<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\Groups;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\GroupUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroupsTest extends TestCase
{
    private AddToGroups $sut;

    protected function setUp(): void
    {
        $this->sut = new AddToGroups(['promotions', 'toto']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AddToGroups::class, $this->sut);
        $this->assertInstanceOf(GroupUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_group_codes(): void
    {
        $this->assertSame(['promotions', 'toto'], $this->sut->groupCodes());
    }

    public function test_it_throws_an_error_if_parameter_is_a_code_is_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AddToGroups(['']);
    }

    public function test_it_throws_an_error_if_parameter_is_an_empty_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AddToGroups([]);
    }
}

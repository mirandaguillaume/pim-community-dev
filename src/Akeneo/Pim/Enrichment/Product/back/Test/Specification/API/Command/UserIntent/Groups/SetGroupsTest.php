<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\Groups;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\GroupUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetGroupsTest extends TestCase
{
    private SetGroups $sut;

    protected function setUp(): void
    {
        $this->sut = new SetGroups(['promotions', 'toto']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetGroups::class, $this->sut);
        $this->assertInstanceOf(GroupUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_group_codes(): void
    {
        $this->assertSame(['promotions', 'toto'], $this->sut->groupCodes());
    }
}

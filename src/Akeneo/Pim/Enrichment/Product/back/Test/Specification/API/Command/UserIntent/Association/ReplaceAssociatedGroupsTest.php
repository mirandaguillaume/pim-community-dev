<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplaceAssociatedGroupsTest extends TestCase
{
    private ReplaceAssociatedGroups $sut;

    protected function setUp(): void
    {
        $this->sut = new ReplaceAssociatedGroups('X_SELL', ['group1', 'group2']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ReplaceAssociatedGroups::class, $this->sut);
        $this->assertInstanceOf(AssociationUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_association_type(): void
    {
        $this->assertSame('X_SELL', $this->sut->associationType());
    }

    public function test_it_returns_the_group_codes(): void
    {
        $this->assertSame(['group1', 'group2'], $this->sut->groupCodes());
    }

    public function test_it_can_only_be_instantiated_with_string_group_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReplaceAssociatedGroups('X_SELL', ['test', 12, false]);
    }

    public function test_it_can_be_instantiated_with_empty_group_codes(): void
    {
        $this->sut = new ReplaceAssociatedGroups('X_SELL', []);
        $this->assertSame([], $this->sut->groupCodes());
    }

    public function test_it_cannot_be_instantiated_if_one_of_the_group_codes_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReplaceAssociatedGroups('X_SELL', ['a', '', 'b']);
    }
}

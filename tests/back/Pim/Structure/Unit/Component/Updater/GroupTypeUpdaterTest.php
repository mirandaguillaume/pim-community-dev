<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Structure\Component\Updater\GroupTypeUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeUpdaterTest extends TestCase
{
    private GroupTypeUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupTypeUpdater();
    }

    public function test_it_is_an_object_updater(): void
    {
        $this->assertInstanceOf(ObjectUpdaterInterface::class, $this->sut);
    }

    public function test_it_throws_when_the_object_to_update_is_not_a_group_type(): void
    {
        $this->expectException(InvalidObjectException::class);

        $this->sut->update(new \stdClass(), []);
    }

    public function test_it_sets_the_code(): void
    {
        $groupType = $this->createMock(GroupTypeInterface::class);
        $groupType->expects($this->once())->method('setCode')->with('variant');

        $this->sut->update($groupType, ['code' => 'variant']);
    }

    public function test_it_sets_a_label_per_locale(): void
    {
        $groupType = $this->createMock(GroupTypeInterface::class);
        $groupType->expects($this->exactly(2))->method('setLocale')
            ->with($this->logicalOr('en_US', 'fr_FR'));
        $groupType->expects($this->exactly(2))->method('setLabel')
            ->with($this->logicalOr('variant', 'variantes'));

        $this->sut->update($groupType, ['label' => ['en_US' => 'variant', 'fr_FR' => 'variantes']]);
    }

    public function test_it_returns_itself_for_chaining(): void
    {
        $groupType = $this->createMock(GroupTypeInterface::class);

        $this->assertSame($this->sut, $this->sut->update($groupType, []));
    }
}

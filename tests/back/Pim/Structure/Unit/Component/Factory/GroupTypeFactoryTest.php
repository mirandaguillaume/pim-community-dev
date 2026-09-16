<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Factory;

use Akeneo\Pim\Structure\Component\Factory\GroupTypeFactory;
use Akeneo\Pim\Structure\Component\Model\GroupType;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeFactoryTest extends TestCase
{
    public function test_it_is_a_simple_factory(): void
    {
        $this->assertInstanceOf(SimpleFactoryInterface::class, new GroupTypeFactory(GroupType::class));
    }

    public function test_it_creates_a_new_instance_of_the_configured_class(): void
    {
        $sut = new GroupTypeFactory(GroupType::class);

        $this->assertInstanceOf(GroupType::class, $sut->create());
    }

    public function test_it_creates_a_distinct_instance_on_each_call(): void
    {
        $sut = new GroupTypeFactory(GroupType::class);

        $this->assertNotSame($sut->create(), $sut->create());
    }
}

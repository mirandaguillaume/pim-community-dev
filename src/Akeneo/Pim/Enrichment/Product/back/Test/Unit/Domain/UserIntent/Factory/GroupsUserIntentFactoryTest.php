<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\GroupsUserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class GroupsUserIntentFactoryTest extends TestCase
{
    private GroupsUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupsUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GroupsUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_groups_user_intent(): void
    {
        $this->assertEquals([new SetGroups(['group1'])], $this->sut->create('groups', ['group1']));
    }

    public function test_it_returns_empty_set_groups_user_intent(): void
    {
        $this->assertEquals([new SetGroups([])], $this->sut->create('groups', []));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('groups', 12);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('groups', null);
    }
}

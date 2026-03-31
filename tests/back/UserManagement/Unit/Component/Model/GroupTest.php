<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Model;

use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\Role;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    private Group $sut;

    protected function setUp(): void
    {
        $this->sut = new Group();
    }

    public function test_it_can_set_a_default_permission(): void
    {
        $this->sut->setDefaultPermission('foo', true);
        $this->assertSame([
                    'foo' => true,
                ], $this->sut->getDefaultPermissions());
    }

    public function test_it_has_a_default_type(): void
    {
        $this->assertSame('default', $this->sut->getType());
    }

    public function test_it_changes_the_default_type(): void
    {
        $this->sut->setType('anything_else');
        $this->assertSame('anything_else', $this->sut->getType());
    }
}

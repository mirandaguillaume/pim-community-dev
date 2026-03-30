<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use PHPUnit\Framework\TestCase;

class PermissionCollectionTest extends TestCase
{
    public function testItCreatesFromNull(): void
    {
        $collection = PermissionCollection::fromArray(null);
        $this->assertNull($collection->normalize());
    }

    public function testItCreatesFromValidPermissions(): void
    {
        $perms = [
            'view' => [['id' => 1, 'label' => 'All']],
            'edit' => [['id' => 2, 'label' => 'Editors']],
            'own' => [['id' => 3, 'label' => 'Owners']],
        ];
        $collection = PermissionCollection::fromArray($perms);
        $this->assertSame($perms, $collection->normalize());
    }

    public function testItRejectsMissingViewKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'edit' => [],
            'own' => [],
        ]);
    }

    public function testItRejectsMissingEditKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => [],
            'own' => [],
        ]);
    }

    public function testItRejectsMissingOwnKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => [],
            'edit' => [],
        ]);
    }

    public function testItRejectsNonArrayViewValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => 'not_an_array',
            'edit' => [],
            'own' => [],
        ]);
    }

    public function testItRejectsNonArrayEditValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => [],
            'edit' => 'not_an_array',
            'own' => [],
        ]);
    }

    public function testItRejectsNonArrayOwnValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => [],
            'edit' => [],
            'own' => 'not_an_array',
        ]);
    }

    public function testGetViewUserGroups(): void
    {
        $perms = [
            'view' => [['id' => 1, 'label' => 'All']],
            'edit' => [],
            'own' => [],
        ];
        $collection = PermissionCollection::fromArray($perms);
        $this->assertSame([['id' => 1, 'label' => 'All']], $collection->getViewUserGroups());
    }

    public function testGetEditUserGroups(): void
    {
        $perms = [
            'view' => [],
            'edit' => [['id' => 2, 'label' => 'Editors']],
            'own' => [],
        ];
        $collection = PermissionCollection::fromArray($perms);
        $this->assertSame([['id' => 2, 'label' => 'Editors']], $collection->getEditUserGroups());
    }

    public function testGetOwnUserGroups(): void
    {
        $perms = [
            'view' => [],
            'edit' => [],
            'own' => [['id' => 3, 'label' => 'Owners']],
        ];
        $collection = PermissionCollection::fromArray($perms);
        $this->assertSame([['id' => 3, 'label' => 'Owners']], $collection->getOwnUserGroups());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use PHPUnit\Framework\TestCase;

class PermissionCollectionTest extends TestCase
{
    public function test_it_creates_from_null(): void
    {
        $collection = PermissionCollection::fromArray(null);
        $this->assertNull($collection->normalize());
    }

    public function test_it_creates_from_valid_permissions(): void
    {
        $perms = [
            'view' => [['id' => 1, 'label' => 'All']],
            'edit' => [['id' => 2, 'label' => 'Editors']],
            'own' => [['id' => 3, 'label' => 'Owners']],
        ];
        $collection = PermissionCollection::fromArray($perms);
        $this->assertSame($perms, $collection->normalize());
    }

    public function test_it_rejects_missing_view_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'edit' => [],
            'own' => [],
        ]);
    }

    public function test_it_rejects_missing_edit_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => [],
            'own' => [],
        ]);
    }

    public function test_it_rejects_missing_own_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => [],
            'edit' => [],
        ]);
    }

    public function test_it_rejects_non_array_view_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => 'not_an_array',
            'edit' => [],
            'own' => [],
        ]);
    }

    public function test_it_rejects_non_array_edit_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => [],
            'edit' => 'not_an_array',
            'own' => [],
        ]);
    }

    public function test_it_rejects_non_array_own_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PermissionCollection::fromArray([
            'view' => [],
            'edit' => [],
            'own' => 'not_an_array',
        ]);
    }

    public function test_get_view_user_groups(): void
    {
        $perms = [
            'view' => [['id' => 1, 'label' => 'All']],
            'edit' => [],
            'own' => [],
        ];
        $collection = PermissionCollection::fromArray($perms);
        $this->assertSame([['id' => 1, 'label' => 'All']], $collection->getViewUserGroups());
    }

    public function test_get_edit_user_groups(): void
    {
        $perms = [
            'view' => [],
            'edit' => [['id' => 2, 'label' => 'Editors']],
            'own' => [],
        ];
        $collection = PermissionCollection::fromArray($perms);
        $this->assertSame([['id' => 2, 'label' => 'Editors']], $collection->getEditUserGroups());
    }

    public function test_get_own_user_groups(): void
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

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Structure\Component\Security\CatalogStructureScopeMapper;
use PHPUnit\Framework\TestCase;

class CatalogStructureScopeMapperTest extends TestCase
{
    private CatalogStructureScopeMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new CatalogStructureScopeMapper();
    }

    public function test_it_is_a_catalog_structure_scope_mapper(): void
    {
        $this->assertInstanceOf(CatalogStructureScopeMapper::class, $this->sut);
        $this->assertInstanceOf(ScopeMapperInterface::class, $this->sut);
    }

    public function test_it_provides_scopes(): void
    {
        $this->assertSame([
                    'read_catalog_structure',
                    'write_catalog_structure',
                ], $this->sut->getScopes());
    }

    public function test_it_provides_acls_that_corresponds_to_the_read_catalog_structure_scope(): void
    {
        $this->assertSame([
                    'pim_api_attribute_list',
                    'pim_api_attribute_group_list',
                    'pim_api_family_list',
                    'pim_api_family_variant_list',
                ], $this->sut->getAcls('read_catalog_structure'));
    }

    public function test_it_provides_acls_that_corresponds_to_the_write_catalog_structure_scope(): void
    {
        $this->assertSame([
                    'pim_api_attribute_edit',
                    'pim_api_attribute_group_edit',
                    'pim_api_family_edit',
                    'pim_api_family_variant_edit',
                ], $this->sut->getAcls('write_catalog_structure'));
    }

    public function test_it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->expectException(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'));
        $this->sut->getAcls('unknown_scope');
    }

    public function test_it_provides_message_that_corresponds_to_read_catalog_structure_scope(): void
    {
        $this->assertSame([
                    'icon' => 'catalog_structure',
                    'type' => 'view',
                    'entities' => 'catalog_structure',
                ], $this->sut->getMessage('read_catalog_structure'));
    }

    public function test_it_provides_message_that_corresponds_to_write_catalog_structure_scope(): void
    {
        $this->assertSame([
                    'icon' => 'catalog_structure',
                    'type' => 'edit',
                    'entities' => 'catalog_structure',
                ], $this->sut->getMessage('write_catalog_structure'));
    }

    public function test_it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->expectException(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'));
        $this->sut->getMessage('unknown_scope');
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_read_catalog_structure_scope(): void
    {
        $this->assertSame([], $this->sut->getLowerHierarchyScopes('read_catalog_structure'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_write_catalog_structure_scope(): void
    {
        $this->assertSame([
                    'read_catalog_structure',
                ], $this->sut->getLowerHierarchyScopes('write_catalog_structure'));
    }

    public function test_it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->expectException(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'));
        $this->sut->getLowerHierarchyScopes('unknown_scope');
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Enrichment\Component\Product\Security\ProductScopeMapper;
use PHPUnit\Framework\TestCase;

class ProductScopeMapperTest extends TestCase
{
    private ProductScopeMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductScopeMapper();
    }

    public function test_it_is_a_product_scope_mapper(): void
    {
        $this->assertInstanceOf(ProductScopeMapper::class, $this->sut);
        $this->assertInstanceOf(ScopeMapperInterface::class, $this->sut);
    }

    public function test_it_provides_all_scopes(): void
    {
        $this->assertSame([
                    'read_products',
                    'write_products',
                    'delete_products',
                ], $this->sut->getScopes());
    }

    public function test_it_provides_acls_that_correspond_to_the_read_product_scope(): void
    {
        $this->assertSame([
                    'pim_api_product_list',
                ], $this->sut->getAcls('read_products'));
    }

    public function test_it_provides_acls_that_correspond_to_the_write_product_scope(): void
    {
        $this->assertSame([
                    'pim_api_product_edit',
                ], $this->sut->getAcls('write_products'));
    }

    public function test_it_provides_acls_that_correspond_to_the_delete_product_scope(): void
    {
        $this->assertSame([
                    'pim_api_product_remove',
                ], $this->sut->getAcls('delete_products'));
    }

    public function test_it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->assertSame([], $this->sut->getAcls('unknown_scope'));
    }

    public function test_it_provides_message_that_correspond_to_the_read_product_scope(): void
    {
        $this->assertSame([
                    'icon' => 'products',
                    'type' => 'view',
                    'entities' => 'products',
                ], $this->sut->getMessage('read_products'));
    }

    public function test_it_provides_message_that_correspond_to_write_product_scope(): void
    {
        $this->assertSame([
                    'icon' => 'products',
                    'type' => 'edit',
                    'entities' => 'products',
                ], $this->sut->getMessage('write_products'));
    }

    public function test_it_provides_message_that_correspond_to_the_delete_product_scope(): void
    {
        $this->assertSame([
                    'icon' => 'products',
                    'type' => 'delete',
                    'entities' => 'products',
                ], $this->sut->getMessage('delete_products'));
    }

    public function test_it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->assertSame([], $this->sut->getMessage('unknown_scope'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_read_product_scope(): void
    {
        $this->assertSame([], $this->sut->getLowerHierarchyScopes('read_products'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_write_product_scope(): void
    {
        $this->assertSame([
                    'read_products',
                ], $this->sut->getLowerHierarchyScopes('write_products'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_delete_product_scope(): void
    {
        $this->assertSame([
                    'read_products',
                    'write_products',
                ], $this->sut->getLowerHierarchyScopes('delete_products'));
    }

    public function test_it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->assertSame([], $this->sut->getLowerHierarchyScopes('unknown_scope'));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Enrichment\Component\Security\CategoriesScopeMapper;
use PHPUnit\Framework\TestCase;

class CategoriesScopeMapperTest extends TestCase
{
    private CategoriesScopeMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new CategoriesScopeMapper();
    }

    public function test_it_is_a_categories_scope_mapper(): void
    {
        $this->assertInstanceOf(CategoriesScopeMapper::class, $this->sut);
        $this->assertInstanceOf(ScopeMapperInterface::class, $this->sut);
    }

    public function test_it_provides_all_scopes(): void
    {
        $this->assertSame([
                    'read_categories',
                    'write_categories',
                ], $this->sut->getScopes());
    }

    public function test_it_provides_acls_that_correspond_to_the_read_categories_scope(): void
    {
        $this->assertSame([
                    'pim_api_category_list',
                ], $this->sut->getAcls('read_categories'));
    }

    public function test_it_provides_acls_that_correspond_to_the_write_categories_scope(): void
    {
        $this->assertSame([
                    'pim_api_category_edit',
                ], $this->sut->getAcls('write_categories'));
    }

    public function test_it_throws_an_exception_when_trying_to_get_acls_if_an_unknown_scope_is_given(): void
    {
        $this->expectException(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'));
        $this->sut->getAcls('unknown_scope');
    }

    public function test_it_provides_message_that_corresponds_to_the_read_categories_scope(): void
    {
        $this->assertSame([
                    'icon' => 'categories',
                    'type' => 'view',
                    'entities' => 'categories',
                ], $this->sut->getMessage('read_categories'));
    }

    public function test_it_provides_message_that_corresponds_to_the_write_categories_scope(): void
    {
        $this->assertSame([
                    'icon' => 'categories',
                    'type' => 'edit',
                    'entities' => 'categories',
                ], $this->sut->getMessage('write_categories'));
    }

    public function test_it_throws_an_exception_when_trying_to_get_message_if_an_unknown_scope_is_given(): void
    {
        $this->expectException(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'));
        $this->sut->getMessage('unknown_scope');
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_read_categories_scope(): void
    {
        $this->assertSame([], $this->sut->getLowerHierarchyScopes('read_categories'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_write_categories_scope(): void
    {
        $this->assertSame([
                    'read_categories',
                ], $this->sut->getLowerHierarchyScopes('write_categories'));
    }

    public function test_it_throws_an_exception_when_trying_to_get_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->expectException(new \InvalidArgumentException('The scope "unknown_scope" does not exist.'));
        $this->sut->getLowerHierarchyScopes('unknown_scope');
    }
}

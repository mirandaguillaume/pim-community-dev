<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Structure\Component\Security\AssociationTypeScopeMapper;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociationTypeScopeMapperTest extends TestCase
{
    private AssociationTypeScopeMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new AssociationTypeScopeMapper();
    }

    public function test_it_is_an_association_type_scope_mapper(): void
    {
        $this->assertInstanceOf(AssociationTypeScopeMapper::class, $this->sut);
        $this->assertInstanceOf(ScopeMapperInterface::class, $this->sut);
    }

    public function test_it_provides_all_scopes(): void
    {
        $this->assertSame([
                    'read_association_types',
                    'write_association_types',
                ], $this->sut->getScopes());
    }

    public function test_it_provides_acls_that_correspond_to_the_read_association_type_scope(): void
    {
        $this->assertSame([
                    'pim_api_association_type_list',
                ], $this->sut->getAcls('read_association_types'));
    }

    public function test_it_provides_acls_that_correspond_to_the_write_association_type_scope(): void
    {
        $this->assertSame([
                    'pim_api_association_type_edit',
                ], $this->sut->getAcls('write_association_types'));
    }

    public function test_it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->assertSame([], $this->sut->getAcls('unknown_scope'));
    }

    public function test_it_provides_message_that_corresponds_to_the_read_association_type_scope(): void
    {
        $this->assertSame([
                    'icon' => 'association_types',
                    'type' => 'view',
                    'entities' => 'association_types',
                ], $this->sut->getMessage('read_association_types'));
    }

    public function test_it_provides_message_that_corresponds_to_the_write_association_type_scope(): void
    {
        $this->assertSame([
                    'icon' => 'association_types',
                    'type' => 'edit',
                    'entities' => 'association_types',
                ], $this->sut->getMessage('write_association_types'));
    }

    public function test_it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->assertSame([], $this->sut->getMessage('unknown_scope'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_read_association_type_scope(): void
    {
        $this->assertSame([], $this->sut->getLowerHierarchyScopes('read_association_types'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_write_association_type_scope(): void
    {
        $this->assertSame([
                    'read_association_types',
                ], $this->sut->getLowerHierarchyScopes('write_association_types'));
    }

    public function test_it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->assertSame([], $this->sut->getLowerHierarchyScopes('unknown_scope'));
    }
}

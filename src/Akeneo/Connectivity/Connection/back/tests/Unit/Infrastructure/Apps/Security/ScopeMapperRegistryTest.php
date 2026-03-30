<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopeMapperRegistryTest extends TestCase
{
    private ScopeMapperInterface|MockObject $productScopes;
    private ScopeMapperInterface|MockObject $catalogStructureScopes;
    private ScopeMapperRegistry $sut;

    protected function setUp(): void
    {
        $this->productScopes = $this->createMock(ScopeMapperInterface::class);
        $this->catalogStructureScopes = $this->createMock(ScopeMapperInterface::class);
        $this->sut = new ScopeMapperRegistry([$this->productScopes, $this->catalogStructureScopes]);
        $this->productScopes->method('getScopes')->willReturn(['read_products', 'write_products']);
        $this->catalogStructureScopes->method('getScopes')->willReturn(['read_catalog_structure', 'write_catalog_structure']);
    }

    public function test_it_is_a_scope_mapper_registry(): void
    {
        $this->assertInstanceOf(ScopeMapperRegistry::class, $this->sut);
    }

    public function test_it_accepts_only_scope_mapper_interface(): void
    {
        $this->expectException(new \InvalidArgumentException(
            \sprintf(
                '%s must implement %s',
                ScopeMapperRegistry::class,
                ScopeMapperInterface::class
            )
        ));
        $this->sut->__construct([new \stdClass()]);
    }

    public function test_it_forbids_to_support_a_scope_more_than_once(): void
    {
        $anyScopeMapper = new class implements ScopeMapperInterface {
            public function getScopes(): array
            {
                return ['read_something'];
            }

            public function getAcls(string $scopeName): array
            {
                return [];
            }

            public function getMessage(string $scopeName): array
            {
                return [];
            }

            public function getLowerHierarchyScopes(string $scopeName): array
            {
                return [];
            }
        }
        ;
        $anotherScopeMapper = new class implements ScopeMapperInterface {
            public function getScopes(): array
            {
                return ['read_something'];
            }

            public function getAcls(string $scopeName): array
            {
                return [];
            }

            public function getMessage(string $scopeName): array
            {
                return [];
            }

            public function getLowerHierarchyScopes(string $scopeName): array
            {
                return [];
            }
        }
        ;
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__construct([$anyScopeMapper, $anotherScopeMapper]);
    }

    public function test_it_provides_all_scopes(): void
    {
        $this->assertSame([
                    'read_products',
                    'write_products',
                    'read_catalog_structure',
                    'write_catalog_structure',
                ], $this->sut->getAllScopes());
    }

    public function test_it_provides_filtered_messages_by_removing_lower_hierarchy_scopes(): void
    {
        $this->productScopes->method('getLowerHierarchyScopes')->with('read_products')->willReturn([]);
        $this->productScopes->method('getLowerHierarchyScopes')->with('write_products')->willReturn(['read_products']);
        $this->catalogStructureScopes->method('getLowerHierarchyScopes')->with('read_catalog_structure')->willReturn([]);
        $this->catalogStructureScopes->method('getLowerHierarchyScopes')->with('write_catalog_structure')->willReturn(['read_catalog_structure']);
        $this->productScopes->method('getMessage')->with('write_products')->willReturn([
                    'icon' => 'write_products_icon',
                    'type' => 'write',
                    'entities' => 'products',
                ]);
        $this->catalogStructureScopes->method('getMessage')->with('write_catalog_structure')->willReturn([
                    'icon' => 'write_catalog_structure_icon',
                    'type' => 'write',
                    'entities' => 'catalog_structure',
                ]);
        $this->assertSame([
                        [
                            'icon' => 'write_catalog_structure_icon',
                            'type' => 'write',
                            'entities' => 'catalog_structure',
                        ],
                        [
                            'icon' => 'write_products_icon',
                            'type' => 'write',
                            'entities' => 'products',
                        ],
                    ], $this->sut->getMessages(['write_products', 'write_catalog_structure', 'read_products', 'read_catalog_structure']));
    }

    public function test_it_provides_complete_acls_by_adding_lower_hierarchy_acls_if_missing(): void
    {
        $this->productScopes->method('getLowerHierarchyScopes')->with('read_products')->willReturn([]);
        $this->productScopes->method('getLowerHierarchyScopes')->with('write_products')->willReturn(['read_products']);
        $this->catalogStructureScopes->method('getLowerHierarchyScopes')->with('write_catalog_structure')->willReturn(['read_catalog_structure']);
        $this->productScopes->method('getAcls')->with('read_products')->willReturn(['pim_api_product_list']);
        $this->productScopes->method('getAcls')->with('write_products')->willReturn(['pim_api_product_edit']);
        $this->catalogStructureScopes->method('getAcls')->with('read_catalog_structure')->willReturn([
                    'pim_api_attribute_list',
                    'pim_api_attribute_group_list',
                    'pim_api_family_list',
                    'pim_api_family_variant_list',
                ]);
        $this->catalogStructureScopes->method('getAcls')->with('write_catalog_structure')->willReturn([
                    'pim_api_attribute_edit',
                    'pim_api_attribute_group_edit',
                    'pim_api_family_edit',
                    'pim_api_family_variant_edit',
                ]);
        $this->assertSame([
                        'pim_api_product_list',
                        'pim_api_attribute_edit',
                        'pim_api_attribute_group_edit',
                        'pim_api_family_edit',
                        'pim_api_family_variant_edit',
                        'pim_api_product_edit',
                        'pim_api_attribute_list',
                        'pim_api_attribute_group_list',
                        'pim_api_family_list',
                        'pim_api_family_variant_list',
                    ], $this->sut->getAcls(['write_products', 'write_catalog_structure', 'read_products']));
    }

    public function test_it_leads_to_an_error_to_ask_for_acl_of_an_unknown_scope(): void
    {
        $this->expectException(new \LogicException('The scope "product_unknown_scope" does not exist.'));
        $this->sut->getAcls(['product_unknown_scope']);
    }

    public function test_it_leads_to_an_error_to_ask_for_message_of_an_unknown_scope(): void
    {
        $this->expectException(new \LogicException('The scope "product_unknown_scope" does not exist.'));
        $this->sut->getMessages(['product_unknown_scope']);
    }
}

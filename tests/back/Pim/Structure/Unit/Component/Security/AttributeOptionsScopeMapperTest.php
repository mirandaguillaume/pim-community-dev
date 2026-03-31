<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Pim\Structure\Component\Security\AttributeOptionsScopeMapper;
use PHPUnit\Framework\TestCase;

class AttributeOptionsScopeMapperTest extends TestCase
{
    private AttributeOptionsScopeMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOptionsScopeMapper();
    }

    public function test_it_is_an_attribute_options_scope_mapper(): void
    {
        $this->assertInstanceOf(AttributeOptionsScopeMapper::class, $this->sut);
        $this->assertInstanceOf(ScopeMapperInterface::class, $this->sut);
    }

    public function test_it_provides_all_scopes(): void
    {
        $this->assertSame([
                    'read_attribute_options',
                    'write_attribute_options',
                ], $this->sut->getScopes());
    }

    public function test_it_provides_acls_that_correspond_to_the_read_attribute_options_scope(): void
    {
        $this->assertSame([
                    'pim_api_attribute_option_list',
                ], $this->sut->getAcls('read_attribute_options'));
    }

    public function test_it_provides_acls_that_correspond_to_the_write_attribute_options_scope(): void
    {
        $this->assertSame([
                    'pim_api_attribute_option_edit',
                ], $this->sut->getAcls('write_attribute_options'));
    }

    public function test_it_throws_an_exception_when_trying_to_get_acls_if_an_unknown_scope_is_given(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('The scope "unknown_scope" does not exist.');
        $this->sut->getAcls('unknown_scope');
    }

    public function test_it_provides_message_that_correspond_to_the_read_attribute_options_scope(): void
    {
        $this->assertSame([
                    'icon' => 'attribute_options',
                    'type' => 'view',
                    'entities' => 'attribute_options',
                ], $this->sut->getMessage('read_attribute_options'));
    }

    public function test_it_provides_message_that_correspond_to_the_write_attribute_options_scope(): void
    {
        $this->assertSame([
                    'icon' => 'attribute_options',
                    'type' => 'edit',
                    'entities' => 'attribute_options',
                ], $this->sut->getMessage('write_attribute_options'));
    }

    public function test_it_throws_an_exception_when_trying_to_get_message_if_an_unknown_scope_is_given(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('The scope "unknown_scope" does not exist.');
        $this->sut->getMessage('unknown_scope');
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_read_attribute_options_scope(): void
    {
        $this->assertSame([], $this->sut->getLowerHierarchyScopes('read_attribute_options'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_write_attribute_options_scope(): void
    {
        $this->assertSame([
                    'read_attribute_options',
                ], $this->sut->getLowerHierarchyScopes('write_attribute_options'));
    }

    public function test_it_throws_an_exception_when_trying_to_get_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('The scope "unknown_scope" does not exist.');
        $this->sut->getLowerHierarchyScopes('unknown_scope');
    }
}

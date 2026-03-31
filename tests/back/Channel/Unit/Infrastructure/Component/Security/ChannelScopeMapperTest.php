<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Security;

use Akeneo\Channel\Infrastructure\Component\Security\ChannelScopeMapper;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use PHPUnit\Framework\TestCase;

class ChannelScopeMapperTest extends TestCase
{
    private ChannelScopeMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new ChannelScopeMapper();
    }

    public function test_it_is_a_channel_scope_mapper(): void
    {
        $this->assertInstanceOf(ChannelScopeMapper::class, $this->sut);
        $this->assertInstanceOf(ScopeMapperInterface::class, $this->sut);
    }

    public function test_it_provides_all_scopes(): void
    {
        $this->assertSame([
                    'read_channel_localization',
                    'read_channel_settings',
                    'write_channel_settings',
                ], $this->sut->getScopes());
    }

    public function test_it_provides_acls_that_correspond_to_the_read_channel_localization_scope(): void
    {
        $this->assertSame([
                    'pim_api_locale_list',
                    'pim_api_currency_list',
                ], $this->sut->getAcls('read_channel_localization'));
    }

    public function test_it_provides_acls_that_correspond_to_the_read_channel_settings_scope(): void
    {
        $this->assertSame([
                    'pim_api_channel_list',
                ], $this->sut->getAcls('read_channel_settings'));
    }

    public function test_it_provides_acls_that_correspond_to_the_write_channel_settings_scope(): void
    {
        $this->assertSame([
                    'pim_api_channel_edit',
                ], $this->sut->getAcls('write_channel_settings'));
    }

    public function test_it_throws_an_exception_when_trying_to_get_acls_if_an_unknown_scope_is_given(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The scope "unknown_scope" does not exist.');
        $this->sut->getAcls('unknown_scope');
    }

    public function test_it_provides_message_that_corresponds_to_the_read_channel_localization_scope(): void
    {
        $this->assertSame([
                    'icon' => 'channel_localization',
                    'type' => 'view',
                    'entities' => 'channel_localization',
                ], $this->sut->getMessage('read_channel_localization'));
    }

    public function test_it_provides_message_that_corresponds_to_the_read_channel_settings_scope(): void
    {
        $this->assertSame([
                    'icon' => 'channel_settings',
                    'type' => 'view',
                    'entities' => 'channel_settings',
                ], $this->sut->getMessage('read_channel_settings'));
    }

    public function test_it_provides_message_that_corresponds_to_the_write_channel_settings_scope(): void
    {
        $this->assertSame([
                    'icon' => 'channel_settings',
                    'type' => 'edit',
                    'entities' => 'channel_settings',
                ], $this->sut->getMessage('write_channel_settings'));
    }

    public function test_it_throws_an_exception_when_trying_to_get_message_if_an_unknown_scope_is_given(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The scope "unknown_scope" does not exist.');
        $this->sut->getMessage('unknown_scope');
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_read_channel_localization_scope(): void
    {
        $this->assertSame([], $this->sut->getLowerHierarchyScopes('read_channel_localization'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_read_channel_settings_scope(): void
    {
        $this->assertSame([], $this->sut->getLowerHierarchyScopes('read_channel_settings'));
    }

    public function test_it_provides_lower_hierarchy_scopes_of_the_write_channel_settings_scope(): void
    {
        $this->assertSame([
                    'read_channel_settings',
                ], $this->sut->getLowerHierarchyScopes('write_channel_settings'));
    }

    public function test_it_throws_an_exception_when_trying_to_get_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The scope "unknown_scope" does not exist.');
        $this->sut->getLowerHierarchyScopes('unknown_scope');
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Apps\Model;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectedAppTest extends TestCase
{
    private ConnectedApp $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectedApp(
            '4028c158-d620-4903-9859-958b66a059e2',
            'Example App',
            ['Scope1', 'Scope2'],
            'someConnectionCode',
            'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'Akeneo',
            'app_123456abcdef',
            'an_username',
            ['E-commerce', 'print'],
            true,
            'Akeneo partner',
            true,
            false,
            true,
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConnectedApp::class, $this->sut);
    }

    public function test_it_returns_the_id(): void
    {
        $this->assertSame('4028c158-d620-4903-9859-958b66a059e2', $this->sut->getId());
    }

    public function test_it_returns_the_name(): void
    {
        $this->assertSame('Example App', $this->sut->getName());
    }

    public function test_it_returns_the_scopes(): void
    {
        $this->assertSame(['Scope1', 'Scope2'], $this->sut->getScopes());
    }

    public function test_it_returns_the_connection_code(): void
    {
        $this->assertSame('someConnectionCode', $this->sut->getConnectionCode());
    }

    public function test_it_returns_the_logo(): void
    {
        $this->assertSame('https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC', $this->sut->getLogo());
    }

    public function test_it_returns_the_author(): void
    {
        $this->assertSame('Akeneo', $this->sut->getAuthor());
    }

    public function test_it_returns_the_user_group_name(): void
    {
        $this->assertSame('app_123456abcdef', $this->sut->getUserGroupName());
    }

    public function test_it_returns_the_connection_username(): void
    {
        $this->assertSame('an_username', $this->sut->getConnectionUsername());
    }

    public function test_it_returns_the_categories(): void
    {
        $this->assertSame(['E-commerce', 'print'], $this->sut->getCategories());
    }

    public function test_it_returns_the_certified_status(): void
    {
        $this->assertSame(true, $this->sut->isCertified());
    }

    public function test_it_returns_the_partner(): void
    {
        $this->assertSame('Akeneo partner', $this->sut->getPartner());
    }

    public function test_it_returns_the_outdated_scopes_status(): void
    {
        $this->assertSame(true, $this->sut->hasOutdatedScopes());
    }

    public function test_it_could_be_pending(): void
    {
        $this->sut = new ConnectedApp(
            '4028c158-d620-4903-9859-958b66a059e2',
            'Example App',
            ['Scope1', 'Scope2'],
            'someConnectionCode',
            'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'Akeneo',
            'app_123456abcdef',
            'an_username',
            ['E-commerce', 'print'],
            true,
            'Akeneo partner',
            false,
            true,
        );
        $this->assertSame(true, $this->sut->isPending());
    }

    public function test_it_is_normalizable(): void
    {
        $this->assertSame([
                    'id' => '4028c158-d620-4903-9859-958b66a059e2',
                    'name' => 'Example App',
                    'scopes' => ['Scope1', 'Scope2'],
                    'connection_code' => 'someConnectionCode',
                    'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                    'author' => 'Akeneo',
                    'user_group_name' => 'app_123456abcdef',
                    'connection_username' => 'an_username',
                    'categories' => ['E-commerce', 'print'],
                    'certified' => true,
                    'partner' => 'Akeneo partner',
                    'is_custom_app' => true,
                    'is_pending' => false,
                    'has_outdated_scopes' => true,
                ], $this->sut->normalize());
    }

    public function test_it_is_neither_a_custom_app_nor_pending_by_default(): void
    {
        $this->sut = new ConnectedApp(
            '4028c158-d620-4903-9859-958b66a059e2',
            'Example App',
            ['Scope1', 'Scope2'],
            'someConnectionCode',
            'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'Akeneo',
            'app_123456abcdef',
            'an_username',
            ['E-commerce', 'print'],
            true,
            'Akeneo partner',
        );
        $this->assertSame([
                    'id' => '4028c158-d620-4903-9859-958b66a059e2',
                    'name' => 'Example App',
                    'scopes' => ['Scope1', 'Scope2'],
                    'connection_code' => 'someConnectionCode',
                    'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                    'author' => 'Akeneo',
                    'user_group_name' => 'app_123456abcdef',
                    'connection_username' => 'an_username',
                    'categories' => ['E-commerce', 'print'],
                    'certified' => true,
                    'partner' => 'Akeneo partner',
                    'is_custom_app' => false,
                    'is_pending' => false,
                    'has_outdated_scopes' => false,
                ], $this->sut->normalize());
    }

    public function test_it_updates_description_properties(): void
    {
        $updated = $this->sut->withUpdatedDescription(
            'New Name',
            'http://example.com/new-logo.png',
            'New Author',
            ['new category'],
            true,
            'Akeneo Premium Partner',
        );
        $this->assertSame([
                    'id' => '4028c158-d620-4903-9859-958b66a059e2',
                    'name' => 'New Name',
                    'scopes' => ['Scope1', 'Scope2'],
                    'connection_code' => 'someConnectionCode',
                    'logo' => 'http://example.com/new-logo.png',
                    'author' => 'New Author',
                    'user_group_name' => 'app_123456abcdef',
                    'connection_username' => 'an_username',
                    'categories' => ['new category'],
                    'certified' => true,
                    'partner' => 'Akeneo Premium Partner',
                    'is_custom_app' => true,
                    'is_pending' => false,
                    'has_outdated_scopes' => true,
                ], $updated->normalize());
    }

    public function test_it_has_not_outdated_scopes_by_default(): void
    {
        $this->sut = new ConnectedApp(
            '4028c158-d620-4903-9859-958b66a059e2',
            'Example App',
            ['Scope1', 'Scope2'],
            'someConnectionCode',
            'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
            'Akeneo',
            'app_123456abcdef',
            'an_username',
            ['E-commerce', 'print'],
            true,
            'Akeneo partner',
        );
        $this->assertSame([
                    'id' => '4028c158-d620-4903-9859-958b66a059e2',
                    'name' => 'Example App',
                    'scopes' => ['Scope1', 'Scope2'],
                    'connection_code' => 'someConnectionCode',
                    'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                    'author' => 'Akeneo',
                    'user_group_name' => 'app_123456abcdef',
                    'connection_username' => 'an_username',
                    'categories' => ['E-commerce', 'print'],
                    'certified' => true,
                    'partner' => 'Akeneo partner',
                    'is_custom_app' => false,
                    'is_pending' => false,
                    'has_outdated_scopes' => false,
                ], $this->sut->normalize());
    }
}

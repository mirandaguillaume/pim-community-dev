<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectedApp;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectedAppInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateConnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App as MarketplaceApp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectedAppTest extends TestCase
{
    private CreateConnectedAppQueryInterface|MockObject $createConnectedAppQuery;
    private CreateConnectedApp $sut;

    protected function setUp(): void
    {
        $this->createConnectedAppQuery = $this->createMock(CreateConnectedAppQueryInterface::class);
        $this->sut = new CreateConnectedApp($this->createConnectedAppQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CreateConnectedApp::class, $this->sut);
        $this->assertInstanceOf(CreateConnectedAppInterface::class, $this->sut);
    }

    public function test_it_maps_the_marketplace_app_and_the_authorization_context_onto_the_connected_app(): void
    {
        $marketplaceApp = MarketplaceApp::fromWebMarketplaceValues([
            'id' => '6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e',
            'name' => 'App prototype',
            'logo' => 'https://marketplace.test/logo.png',
            'author' => 'Akeneo',
            'partner' => 'Akeneo Partner',
            'url' => 'https://marketplace.test/app-prototype',
            'categories' => ['E-commerce', 'Print'],
            'certified' => true,
            'activate_url' => 'https://marketplace.test/activate',
            'callback_url' => 'https://marketplace.test/callback',
        ]);

        $persistedApp = null;
        $this->createConnectedAppQuery
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function (ConnectedApp $app) use (&$persistedApp): void {
                $persistedApp = $app;
            });

        $connectedApp = $this->sut->execute(
            $marketplaceApp,
            ['read_products', 'write_products', 'delete_products'],
            'a_connection_code',
            'app_a_user_group_name',
            'a_connection_username',
        );

        $this->assertSame($persistedApp, $connectedApp);
        $this->assertSame('6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e', $connectedApp->getId());
        $this->assertSame('App prototype', $connectedApp->getName());
        $this->assertSame(['read_products', 'write_products', 'delete_products'], $connectedApp->getScopes());
        $this->assertSame('a_connection_code', $connectedApp->getConnectionCode());
        $this->assertSame('https://marketplace.test/logo.png', $connectedApp->getLogo());
        $this->assertSame('Akeneo', $connectedApp->getAuthor());
        $this->assertSame('app_a_user_group_name', $connectedApp->getUserGroupName());
        $this->assertSame('a_connection_username', $connectedApp->getConnectionUsername());
        $this->assertSame(['E-commerce', 'Print'], $connectedApp->getCategories());
        $this->assertTrue($connectedApp->isCertified());
        $this->assertSame('Akeneo Partner', $connectedApp->getPartner());
        $this->assertFalse($connectedApp->isCustomApp());
    }

    public function test_it_maps_a_marketplace_app_without_optional_description_values(): void
    {
        $marketplaceApp = MarketplaceApp::fromCustomAppValues([
            'id' => 'a_custom_app_id',
            'name' => 'A custom app',
            'activate_url' => 'https://custom.test/activate',
            'callback_url' => 'https://custom.test/callback',
        ]);

        $persistedApp = null;
        $this->createConnectedAppQuery
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function (ConnectedApp $app) use (&$persistedApp): void {
                $persistedApp = $app;
            });

        $connectedApp = $this->sut->execute(
            $marketplaceApp,
            [],
            'another_connection_code',
            'app_another_user_group_name',
            'another_connection_username',
        );

        $this->assertSame($persistedApp, $connectedApp);
        $this->assertNull($connectedApp->getLogo());
        $this->assertNull($connectedApp->getAuthor());
        $this->assertNull($connectedApp->getPartner());
        $this->assertSame([], $connectedApp->getCategories());
        $this->assertFalse($connectedApp->isCertified());
        $this->assertSame([], $connectedApp->getScopes());
    }

    public function test_it_does_not_return_a_connected_app_when_the_persistence_fails(): void
    {
        $marketplaceApp = MarketplaceApp::fromCustomAppValues([
            'id' => 'a_custom_app_id',
            'name' => 'A custom app',
            'activate_url' => 'https://custom.test/activate',
            'callback_url' => 'https://custom.test/callback',
        ]);

        $this->createConnectedAppQuery
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \RuntimeException('insert failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('insert failed');

        $this->sut->execute(
            $marketplaceApp,
            [],
            'a_connection_code',
            'app_a_user_group_name',
            'a_connection_username',
        );
    }
}

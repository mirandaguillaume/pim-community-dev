<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAppQueryTest extends TestCase
{
    private WebMarketplaceApiInterface|MockObject $webMarketplaceApi;
    private GetCustomAppQuery|MockObject $getCustomAppQuery;
    private GetAppQuery $sut;

    protected function setUp(): void
    {
        $this->webMarketplaceApi = $this->createMock(WebMarketplaceApiInterface::class);
        $this->getCustomAppQuery = $this->createMock(GetCustomAppQuery::class);
        $this->sut = new GetAppQuery(
            $this->webMarketplaceApi,
            $this->getCustomAppQuery,
        );
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(GetAppQuery::class, $this->sut);
    }

    public function test_it_returns_a_known_marketplace_app(): void
    {
        $this->webMarketplaceApi->method('getApp')->with('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn([
                    'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                    'name' => 'Akeneo Shopware 6 App by EIKONA Media',
                    'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                    'author' => 'EIKONA Media GmbH',
                    'partner' => 'Akeneo Preferred Partner',
                    'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The app uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                    'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
                    'categories' => [
                        'E-commerce',
                    ],
                    'certified' => false,
                    'activate_url' => 'http://shopware.example.com/activate',
                    'callback_url' => 'http://shopware.example.com/callback',
                ]);
        $this->assertEquals(App::fromWebMarketplaceValues([
                        'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                        'name' => 'Akeneo Shopware 6 App by EIKONA Media',
                        'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                        'author' => 'EIKONA Media GmbH',
                        'partner' => 'Akeneo Preferred Partner',
                        'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The app uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                        'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
                        'categories' => [
                            'E-commerce',
                        ],
                        'certified' => false,
                        'activate_url' => 'http://shopware.example.com/activate',
                        'callback_url' => 'http://shopware.example.com/callback',
                    ]), $this->sut->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9'));
    }

    public function test_it_returns_a_known_marketplace_app_even_when_custom_app_is_not_found(): void
    {
        $this->getCustomAppQuery->method('execute')->with('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn(null);
        $this->webMarketplaceApi->method('getApp')->with('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn([
                    'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                    'name' => 'Akeneo Shopware 6 App by EIKONA Media',
                    'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                    'author' => 'EIKONA Media GmbH',
                    'partner' => 'Akeneo Preferred Partner',
                    'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The app uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                    'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
                    'categories' => [
                        'E-commerce',
                    ],
                    'certified' => false,
                    'activate_url' => 'http://shopware.example.com/activate',
                    'callback_url' => 'http://shopware.example.com/callback',
                ]);
        $this->assertEquals(App::fromWebMarketplaceValues([
                        'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                        'name' => 'Akeneo Shopware 6 App by EIKONA Media',
                        'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                        'author' => 'EIKONA Media GmbH',
                        'partner' => 'Akeneo Preferred Partner',
                        'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The app uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                        'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
                        'categories' => [
                            'E-commerce',
                        ],
                        'certified' => false,
                        'activate_url' => 'http://shopware.example.com/activate',
                        'callback_url' => 'http://shopware.example.com/callback',
                    ]), $this->sut->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9'));
    }

    public function test_it_returns_null_if_unknown_marketplace_app(): void
    {
        $this->webMarketplaceApi->method('getApp')->with('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn(null);
        $this->assertNull($this->sut->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9'));
    }

    public function test_it_returns_a_known_custom_app(): void
    {
        $this->getCustomAppQuery->method('execute')->with('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn([
                    'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                    'name' => 'My Test App',
                    'author' => 'John Doe',
                    'activate_url' => 'http://shopware.example.com/activate',
                    'callback_url' => 'http://shopware.example.com/callback',
                ]);
        $this->assertEquals(App::fromCustomAppValues([
                        'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                        'name' => 'My Test App',
                        'author' => 'John Doe',
                        'activate_url' => 'http://shopware.example.com/activate',
                        'callback_url' => 'http://shopware.example.com/callback',
                    ]), $this->sut->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9'));
    }

    public function test_it_returns_null_if_unknown_custom_app_and_marketplace_app(): void
    {
        $this->getCustomAppQuery->method('execute')->with('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn(null);
        $this->webMarketplaceApi->method('getApp')->with('100eedac-ff5c-497b-899d-e2d64b6c59f9')->willReturn(null);
        $this->assertNull($this->sut->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9'));
    }
}

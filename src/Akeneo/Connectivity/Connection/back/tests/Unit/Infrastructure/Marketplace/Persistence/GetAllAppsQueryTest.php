<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAllConnectedAppsPublicIdsInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAllPendingAppsPublicIdsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetAllAppsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllAppsQueryTest extends TestCase
{
    private WebMarketplaceApiInterface|MockObject $webMarketplaceApi;
    private GetAllConnectedAppsPublicIdsInterface|MockObject $getAllConnectedAppsPublicIdsQuery;
    private GetAllPendingAppsPublicIdsQueryInterface|MockObject $getAllPendingAppsPublicIdsQuery;
    private GetAllAppsQuery $sut;

    private array $items;

    protected function setUp(): void
    {
        $this->webMarketplaceApi = $this->createMock(WebMarketplaceApiInterface::class);
        $this->getAllConnectedAppsPublicIdsQuery = $this->createMock(GetAllConnectedAppsPublicIdsInterface::class);
        $this->getAllPendingAppsPublicIdsQuery = $this->createMock(GetAllPendingAppsPublicIdsQueryInterface::class);
        $this->sut = new GetAllAppsQuery($this->webMarketplaceApi, $this->getAllConnectedAppsPublicIdsQuery, $this->getAllPendingAppsPublicIdsQuery, 2);
        $this->items = [
            [
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 App by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-App" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware.',
                'url' => 'https://marketplace.akeneo.com/app/akeneo-shopware-6-app-eikona-media',
                'categories' => ['E-commerce'],
                'certified' => false,
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ],
            [
                'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                'name' => 'Akeneo PIM App for Shopify',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/shopify-app-logo-1200x.png?itok=mASOVlwC',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'SaaS software from StrikeTru.',
                'url' => 'https://marketplace.akeneo.com/app/akeneo-pim-app-shopify',
                'categories' => ['E-commerce'],
                'certified' => false,
                'activate_url' => 'http://shopify.example.com/activate',
                'callback_url' => 'http://shopify.example.com/callback',
            ],
            [
                'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569b',
                'name' => 'Akeneo PIM App for Shopify 2',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/shopify-app-logo-1200x.png?itok=mASOVlwC',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'SaaS software from StrikeTru.',
                'url' => 'https://marketplace.akeneo.com/app/akeneo-pim-app-shopify',
                'categories' => ['E-commerce'],
                'certified' => false,
                'activate_url' => 'http://shopify.example.com/activate',
                'callback_url' => 'http://shopify.example.com/callback',
            ],
            [
                'id' => 'blblblblbl-378e-41a5-babb-ca0ec0af569b',
                'name' => 'Akeneo PIM App for Shopify 3',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/app_logo_large/public/app-logos/shopify-app-logo-1200x.png?itok=mASOVlwC',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'SaaS software from StrikeTru.',
                'url' => 'https://marketplace.akeneo.com/app/akeneo-pim-app-shopify',
                'categories' => ['E-commerce'],
                'certified' => false,
                'activate_url' => 'http://shopify.example.com/activate',
                'callback_url' => 'http://shopify.example.com/callback',
            ],
        ];

        // Configure mock to return items in pages of 2
        $this->webMarketplaceApi->method('getApps')->willReturnCallback(function (int $offset, int $limit) {
            $slice = \array_slice($this->items, $offset, $limit);
            return [
                'total' => \count($this->items),
                'offset' => $offset,
                'limit' => $limit,
                'items' => $slice,
            ];
        });
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(GetAllAppsQuery::class, $this->sut);
    }

    public function test_it_executes_and_returns_app_result(): void
    {
        $this->getAllPendingAppsPublicIdsQuery->method('execute')->willReturn([]);
        $this->getAllConnectedAppsPublicIdsQuery->method('execute')->willReturn([]);
        $result = $this->sut->execute();
        $this->assertSame(4, $result->normalize()['total']);
        $this->assertCount(4, $result->normalize()['apps']);
    }

    public function test_it_sets_connected_to_true_on_connected_apps(): void
    {
        $this->getAllPendingAppsPublicIdsQuery->method('execute')->willReturn([]);
        $this->getAllConnectedAppsPublicIdsQuery->method('execute')->willReturn([
                    $this->items[0]['id'],
                    $this->items[2]['id'],
                ]);
        $result = $this->sut->execute();
        $normalized = $result->normalize();
        $apps = $normalized['apps'];
        // Find apps by id and check connected status
        $appById = [];
        foreach ($apps as $app) {
            $appById[$app['id']] = $app;
        }
        $this->assertTrue($appById[$this->items[0]['id']]['connected']);
        $this->assertFalse($appById[$this->items[1]['id']]['connected']);
        $this->assertTrue($appById[$this->items[2]['id']]['connected']);
    }

    public function test_it_sets_pending_to_true_on_pending_apps(): void
    {
        $this->getAllPendingAppsPublicIdsQuery->method('execute')->willReturn([
                    $this->items[1]['id'],
                    $this->items[3]['id'],
                ]);
        $this->getAllConnectedAppsPublicIdsQuery->method('execute')->willReturn([
                    $this->items[0]['id'],
                ]);
        $result = $this->sut->execute();
        $normalized = $result->normalize();
        $apps = $normalized['apps'];
        $appById = [];
        foreach ($apps as $app) {
            $appById[$app['id']] = $app;
        }
        $this->assertTrue($appById[$this->items[0]['id']]['connected']);
        $this->assertFalse($appById[$this->items[0]['id']]['isPending']);
        $this->assertFalse($appById[$this->items[1]['id']]['connected']);
        $this->assertTrue($appById[$this->items[1]['id']]['isPending']);
    }
}

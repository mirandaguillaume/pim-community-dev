<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllExtensionsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetAllExtensionsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllExtensionsQueryTest extends TestCase
{
    private const PAGINATION = 10;

    private WebMarketplaceApiInterface|MockObject $webMarketplaceApi;
    private GetAllExtensionsQuery $sut;

    private array $items;

    protected function setUp(): void
    {
        $this->webMarketplaceApi = $this->createMock(WebMarketplaceApiInterface::class);
        $this->sut = new GetAllExtensionsQuery($this->webMarketplaceApi, self::PAGINATION);
        $this->items = [
            [
                'id' => '3881aefa-16a3-4b4f-94c3-0d6e858b60b8',
                'name' => 'Shopify connector',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/extension-logos/Image.jpg',
                'author' => 'Ideatarmac',
                'partner' => 'Akeneo Partner',
                'description' => 'Our Shopify Akeneo Connector.',
                'url' => 'https://marketplace.akeneo.com/extension/shopify-connector',
                'categories' => ['E-commerce'],
                'certified' => false,
            ],
            [
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/extension-logos/akeneo-to-shopware6-eimed_0.jpg',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'description_1',
                'url' => 'url_1',
                'categories' => ['E-commerce'],
                'certified' => false,
            ],
            [
                'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                'name' => 'Akeneo PIM Connector for Shopify',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/extension-logos/shopify-connector-logo-1200x.png',
                'author' => 'StrikeTru',
                'partner' => 'Akeneo Partner',
                'description' => 'description_2',
                'url' => 'url_2',
                'categories' => ['E-commerce'],
                'certified' => false,
            ],
        ];
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetAllExtensionsQuery::class, $this->sut);
    }

    public function test_it_execute_and_returns_extension_result(): void
    {
        $this->webMarketplaceApi->method('getExtensions')->willReturnCallback(function (int $offset, int $limit) {
            $slice = \array_slice($this->items, $offset, $limit);
            return [
                'total' => \count($this->items),
                'offset' => $offset,
                'limit' => $limit,
                'items' => $slice,
            ];
        });

        $result = $this->sut->execute();
        $this->assertSame(3, $result->normalize()['total']);
        $this->assertCount(3, $result->normalize()['extensions']);
    }
}

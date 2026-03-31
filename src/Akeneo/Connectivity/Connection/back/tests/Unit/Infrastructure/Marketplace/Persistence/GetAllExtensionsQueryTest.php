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

    protected function setUp(): void
    {
        $this->webMarketplaceApi = $this->createMock(WebMarketplaceApiInterface::class);
        $this->sut = new GetAllExtensionsQuery($this->webMarketplaceApi, self::PAGINATION);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetAllExtensionsQuery::class, $this->sut);
    }

    public function test_it_execute_and_returns_extension_result(): void
    {
        $items = [
                    [
                        'id' => '3881aefa-16a3-4b4f-94c3-0d6e858b60b8',
                        'name' => 'Shopify connector',
                        'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/Image%20from%20iOS.jpg?itok=1OF5jl0j',
                        'author' => 'Ideatarmac',
                        'partner' => 'Akeneo Partner',
                        'description' => 'Our Shopify Akeneo Connector eases your business by refining, transforming, and publishing relevant products, images, videos, and attributes between Akeneo and Shopify.Ideatarmac\u2019s Shopify connector is a cloud based technology and has compatibility to the widest and latest range of Akeneo editions from Community to Enterprise to Growth Edition. Our aim is to make your integration the simplest possible and reduce the routine data management effort up to 70%.',
                        'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-connector',
                        'categories' => ['E-commerce'],
                        'certified' => false,
                    ],
                    [
                        'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                        'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                        'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                        'author' => 'EIKONA Media GmbH',
                        'partner' => 'Akeneo Preferred Partner',
                        'description' => 'description_1',
                        'url' => 'url_1',
                        'categories' => [
                            'E-commerce',
                        ],
                        'certified' => false,
                    ],
                    [
                        'id' => 'b18561ff-378e-41a5-babb-ca0ec0af569a',
                        'name' => 'Akeneo PIM Connector for Shopify',
                        'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/shopify-connector-logo-1200x.png?itok=mASOVlwC',
                        'author' => 'StrikeTru',
                        'partner' => 'Akeneo Partner',
                        'description' => 'description_2',
                        'url' => 'url_2',
                        'categories' => [
                            'E-commerce',
                        ],
                        'certified' => false,
                    ],
                ];
        $this->webMarketplaceApi->method('getExtensions')->with(0, 2);
        $this->webMarketplaceApi->method('getExtensions')->with(2, 2);
        $this->assertEquals(GetAllExtensionsResult::create(3, \array_map(fn ($item): Extension => Extension::fromWebMarketplaceValues($item), $items)), $this->sut->execute());
    }
}

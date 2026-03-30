<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Marketplace\Model;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ExtensionTest extends TestCase
{
    private Extension $sut;

    protected function setUp(): void
    {
        $this->sut = Extension::fromWebMarketplaceValues([
                'id' => '3881aefa-16a3-4b4f-94c3-0d6e858b60b8',
                'name' => 'Shopify connector',
                'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/Image%20from%20iOS.jpg?itok=1OF5jl0j',
                'author' => 'Ideatarmac',
                'partner' => 'Akeneo Partner',
                'description' => 'Our Shopify Akeneo Connector eases your business by refining, transforming, and publishing relevant products, images, videos, and attributes between Akeneo and Shopify.Ideatarmac\u2019s Shopify connector is a cloud based technology and has compatibility to the widest and latest range of Akeneo editions from Community to Enterprise to Growth Edition. Our aim is to make your integration the simplest possible and reduce the routine data management effort up to 70%.',
                'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-connector',
                'categories' => ['E-commerce'],
                'certified' => false,
            ], );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Extension::class, $this->sut);
    }

    public function test_it_is_normalizable(): void
    {
        $this->assertSame([
                    'id' => '3881aefa-16a3-4b4f-94c3-0d6e858b60b8',
                    'name' => 'Shopify connector',
                    'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/Image%20from%20iOS.jpg?itok=1OF5jl0j',
                    'author' => 'Ideatarmac',
                    'partner' => 'Akeneo Partner',
                    'description' => 'Our Shopify Akeneo Connector eases your business by refining, transforming, and publishing relevant products, images, videos, and attributes between Akeneo and Shopify.Ideatarmac\u2019s Shopify connector is a cloud based technology and has compatibility to the widest and latest range of Akeneo editions from Community to Enterprise to Growth Edition. Our aim is to make your integration the simplest possible and reduce the routine data management effort up to 70%.',
                    'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-connector',
                    'categories' => ['E-commerce'],
                    'certified' => false,
                ], $this->sut->normalize());
    }

    public function test_it_adds_analytics(): void
    {
        $this->assertSame([
                    'id' => '3881aefa-16a3-4b4f-94c3-0d6e858b60b8',
                    'name' => 'Shopify connector',
                    'logo' => 'https:\/\/marketplace.akeneo.com\/sites\/default\/files\/styles\/extension_logo_large\/public\/extension-logos\/Image%20from%20iOS.jpg?itok=1OF5jl0j',
                    'author' => 'Ideatarmac',
                    'partner' => 'Akeneo Partner',
                    'description' => 'Our Shopify Akeneo Connector eases your business by refining, transforming, and publishing relevant products, images, videos, and attributes between Akeneo and Shopify.Ideatarmac\u2019s Shopify connector is a cloud based technology and has compatibility to the widest and latest range of Akeneo editions from Community to Enterprise to Growth Edition. Our aim is to make your integration the simplest possible and reduce the routine data management effort up to 70%.',
                    'url' => 'https:\/\/marketplace.akeneo.com\/extension\/shopify-connector?utm_campaign=foobar',
                    'categories' => ['E-commerce'],
                    'certified' => false,
                ], $this->sut->withAnalytics([
                    'utm_campaign' => 'foobar',
                ])->normalize());
    }
}

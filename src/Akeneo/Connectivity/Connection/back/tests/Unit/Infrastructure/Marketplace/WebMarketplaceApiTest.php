<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceApiTest extends TestCase
{
    private Client|MockObject $client;
    private WebMarketplaceAliasesInterface|MockObject $webMarketplaceAliases;
    private LoggerInterface|MockObject $logger;
    private FeatureFlag|MockObject $fakeAppsFeatureFlag;
    private WebMarketplaceApi $sut;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->webMarketplaceAliases = $this->createMock(WebMarketplaceAliasesInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->fakeAppsFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->sut = new WebMarketplaceApi($this->client, $this->webMarketplaceAliases, $this->logger, $this->fakeAppsFeatureFlag);
        $this->sut->setFixturePath(__DIR__ . '/../../spec/Infrastructure/Marketplace/fixtures/');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(WebMarketplaceApi::class, $this->sut);
        $this->assertInstanceOf(WebMarketplaceApiInterface::class, $this->sut);
    }

    public function test_it_returns_extensions(): void
    {
        $response = $this->createMock(Response::class);
        $stream = $this->createMock(StreamInterface::class);

        $expectedResponse = [
                    'total' => 3,
                    'limit' => 10,
                    'offset' => 0,
                    'items' => [
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
                    ],
                ];
        $this->webMarketplaceAliases->method('getEdition')->willReturn('community-edition');
        $this->webMarketplaceAliases->method('getVersion')->willReturn('5.0');
        $stream->method('getContents')->willReturn(\json_encode($expectedResponse));
        $response->method('getBody')->willReturn($stream);
        $this->client->method('request')->with('GET', '/api/1.0/extensions', [
                    'query' => [
                        'extension_type' => 'connector',
                        'edition' => 'community-edition',
                        'version' => '5.0',
                        'offset' => 0,
                        'limit' => 10,
                    ],
                ])->willReturn($response);
        $extensions = ($this->sut->getExtensions());
        Assert::assertEquals($expectedResponse, $extensions);
    }

    public function test_it_returns_true_when_a_code_challenge_is_valid(): void
    {
        $response = $this->createMock(Response::class);
        $stream = $this->createMock(StreamInterface::class);

        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';
        $this->client->method('request')->with('POST', '/api/1.0/app/90741597-54c5-48a1-98da-a68e7ee0a715/challenge', [
                    'json' => [
                        'code_identifier' => $codeIdentifier,
                        'code_challenge' => $codeChallenge,
                    ],
                ])->willReturn($response);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn(\json_encode(['valid' => true]));
        $this->assertSame(true, $this->sut->validateCodeChallenge($appId, $codeIdentifier, $codeChallenge));
    }

    public function test_it_returns_false_when_a_code_challenge_is_invalid(): void
    {
        $response = $this->createMock(Response::class);
        $stream = $this->createMock(StreamInterface::class);

        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';
        $this->client->method('request')->with('POST', '/api/1.0/app/90741597-54c5-48a1-98da-a68e7ee0a715/challenge', [
                    'json' => [
                        'code_identifier' => $codeIdentifier,
                        'code_challenge' => $codeChallenge,
                    ],
                ])->willReturn($response);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn(\json_encode(['valid' => false]));
        $this->assertSame(false, $this->sut->validateCodeChallenge($appId, $codeIdentifier, $codeChallenge));
    }

    public function test_it_returns_false_when_a_code_challenge_request_fails(): void
    {
        $response = $this->createMock(Response::class);
        $stream = $this->createMock(StreamInterface::class);

        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $codeIdentifier = '2DkpkyHfgm';
        $codeChallenge = 'JN2eVHPP4F';
        $this->client->method('request')->with('POST', '/api/1.0/app/90741597-54c5-48a1-98da-a68e7ee0a715/challenge', [
                    'json' => [
                        'code_identifier' => $codeIdentifier,
                        'code_challenge' => $codeChallenge,
                    ],
                ])->willReturn($response);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getBody')->willReturn($stream);
        $stream->method('getContents')->willReturn(\json_encode(['error' => 'Not found.']));
        $this->assertSame(false, $this->sut->validateCodeChallenge($appId, $codeIdentifier, $codeChallenge));
    }

    public function test_it_returns_apps(): void
    {
        $response = $this->createMock(Response::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->fakeAppsFeatureFlag->method('isEnabled')->willReturn(false);
        $expectedResponse = [
                    'total' => 2,
                    'limit' => 10,
                    'offset' => 0,
                    'items' => [
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
                    ],
                ];
        $this->webMarketplaceAliases->method('getEdition')->willReturn('community-edition');
        $this->webMarketplaceAliases->method('getVersion')->willReturn('5.0');
        $stream->method('getContents')->willReturn(\json_encode($expectedResponse));
        $response->method('getBody')->willReturn($stream);
        $this->client->method('request')->with('GET', '/api/1.0/extensions', [
                    'query' => [
                        'extension_type' => 'app',
                        'edition' => 'community-edition',
                        'version' => '5.0',
                        'offset' => 0,
                        'limit' => 10,
                    ],
                ])->willReturn($response);
        $apps = ($this->sut->getApps());
        Assert::assertEquals($expectedResponse, $apps);
    }

    public function test_it_returns_fake_apps(): void
    {
        $this->fakeAppsFeatureFlag->method('isEnabled')->willReturn(true);
        $extensions = ($this->sut->getApps());
        Assert::assertEquals([
                    'total' => 2,
                    'offset' => 0,
                    'limit' => 120,
                    'items' => [
                        [
                            'id' => '6ff52991-1144-45cf-933a-5c45ae58e71a',
                            'name' => 'Yell extension',
                            'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                            'author' => 'Akeneo connectivity team',
                            'partner' => 'Akeneo',
                            'description' => 'Developed by the Akeneo team to demonstrate the different steps of an app activation. You can try it safely!',
                            'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
                            'categories' => [
                                'E-commerce',
                            ],
                            'certified' => true,
                            'activate_url' => 'https://yell-extension-t2omu7tdaq-uc.a.run.app/activate',
                            'callback_url' => 'https://yell-extension-t2omu7tdaq-uc.a.run.app/oauth2',
                        ],
                        [
                            "id" => "b213fec1-02e6-4f88-9e2e-0ac86fa34d92",
                            "author" => "Akeneo",
                            "partner" => null,
                            "name" => "Akeneo Demo App in docker",
                            "activate_url" => "http://172.17.0.1:8090",
                            "callback_url" => "http://172.17.0.1:8090/callback",
                            "categories" => [
                                "Advertising",
                            ],
                            "logo" => "https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-ico-app-demoapp_0.jpeg?itok=U7JH_xFa",
                            "description" => "Apps are the best way to connect the third-party technology that you need to your Akeneo platform. The Akeneo Demo App will allow you to test out the connection experience. You can connect your PIM with the Demo App to see just how easy it is!",
                            "certified" => false,
                            "url" => "https://marketplace.akeneo.com/extension/akeneo-demo-app",
                        ],
                    ],
                ], $extensions);
    }
}

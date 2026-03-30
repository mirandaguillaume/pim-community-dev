<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Marketplace\DTO;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllAppsResultTest extends TestCase
{
    private App|MockObject $app;
    private GetAllAppsResult $sut;

    protected function setUp(): void
    {
        $this->app = $this->createMock(App::class);
        $this->sut = GetAllAppsResult::create(12, [$app]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetAllAppsResult::class, $this->sut);
    }

    public function test_it_is_normalizable(): void
    {
        $this->app->method('normalize')->willReturn([
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                ]);
        $this->assertSame([
                    'total' => 12,
                    'apps' => [
                        [
                            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                        ],
                    ],
                ], $this->sut->normalize());
    }

    public function test_it_adds_analytics(): void
    {
        $appWithAnalytics = $this->createMock(App::class);

        $queryParameters = [
                    'utm_campaign' => 'foobar',
                ];
        $this->app->method('normalize')->willReturn([
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                    'url' => 'https://marketplace.akeneo.com/extension/shopify-connector',
                ]);
        $appWithAnalytics->method('normalize')->willReturn([
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                    'url' => 'https://marketplace.akeneo.com/extension/shopify-connector?utm_campaign=foobar',
                ]);
        $this->app->method('withAnalytics')->with($queryParameters)->willReturn($appWithAnalytics);
        $this->assertEquals([
                    'total' => 12,
                    'apps' => [
                        [
                            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                            'url' => 'https://marketplace.akeneo.com/extension/shopify-connector?utm_campaign=foobar',
                        ],
                    ],
                ], $this->sut->withAnalytics($queryParameters)->normalize());
    }

    public function test_it_adds_the_pim_url(): void
    {
        $appWithPimUrl = $this->createMock(App::class);

        $queryParameters = [
                    'pim_url' => 'http://pim',
                ];
        $this->app->method('normalize')->willReturn([
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                    'activate_url' => 'https://extension.example/activate',
                ]);
        $appWithPimUrl->method('normalize')->willReturn([
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                    'activate_url' => 'https://extension.example/activate?pim_url=http%3A%2F%2Fpim',
                ]);
        $this->app->method('withPimUrlSource')->with($queryParameters)->willReturn($appWithPimUrl);
        $this->assertEquals([
                    'total' => 12,
                    'apps' => [
                        [
                            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                            'activate_url' => 'https://extension.example/activate?pim_url=http%3A%2F%2Fpim',
                        ],
                    ],
                ], $this->sut->withPimUrlSource($queryParameters)->normalize());
    }
}

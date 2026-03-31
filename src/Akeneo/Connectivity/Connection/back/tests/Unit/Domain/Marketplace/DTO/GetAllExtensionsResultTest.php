<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Marketplace\DTO;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllExtensionsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllExtensionsResultTest extends TestCase
{
    private Extension|MockObject $extension;
    private GetAllExtensionsResult $sut;

    protected function setUp(): void
    {
        $this->extension = $this->createMock(Extension::class);
        $this->sut = GetAllExtensionsResult::create(12, [$this->extension]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetAllExtensionsResult::class, $this->sut);
    }

    public function test_it_is_normalizable(): void
    {
        $this->extension->method('normalize')->willReturn([
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                ]);
        $this->assertSame([
                    'total' => 12,
                    'extensions' => [
                        [
                            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                        ],
                    ],
                ], $this->sut->normalize());
    }

    public function test_it_adds_analytics(): void
    {
        $extensionWithAnalytics = $this->createMock(Extension::class);

        $queryParameters = [
                    'utm_campaign' => 'foobar',
                ];
        $this->extension->method('normalize')->willReturn([
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                    'url' => 'https://marketplace.akeneo.com/extension/shopify-connector',
                ]);
        $extensionWithAnalytics->method('normalize')->willReturn([
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                    'url' => 'https://marketplace.akeneo.com/extension/shopify-connector?utm_campaign=foobar',
                ]);
        $this->extension->method('withAnalytics')->with($queryParameters)->willReturn($extensionWithAnalytics);
        $this->assertEquals([
                    'total' => 12,
                    'extensions' => [
                        [
                            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                            'url' => 'https://marketplace.akeneo.com/extension/shopify-connector?utm_campaign=foobar',
                        ],
                    ],
                ], $this->sut->withAnalytics($queryParameters)->normalize());
    }
}

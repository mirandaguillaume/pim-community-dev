<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppUrlGeneratorTest extends TestCase
{
    private PimUrl|MockObject $pimUrl;
    private AppUrlGenerator $sut;

    protected function setUp(): void
    {
        $this->pimUrl = $this->createMock(PimUrl::class);
        $this->sut = new AppUrlGenerator($this->pimUrl);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AppUrlGenerator::class, $this->sut);
    }

    public function test_it_generates_app_query_parameters(): void
    {
        $this->pimUrl->method('getPimUrl')->willReturn('http://my-akeneo.test');
        $this->assertSame(['pim_url' => 'http://my-akeneo.test'], $this->sut->getAppQueryParameters());
    }
}

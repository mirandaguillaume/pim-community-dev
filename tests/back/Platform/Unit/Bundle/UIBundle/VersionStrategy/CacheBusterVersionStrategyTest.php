<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\UIBundle\VersionStrategy;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\VersionStrategy\CacheBusterVersionStrategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheBusterVersionStrategyTest extends TestCase
{
    private VersionProviderInterface|MockObject $versionProvider;
    private CacheBusterVersionStrategy $sut;

    protected function setUp(): void
    {
        $this->versionProvider = $this->createMock(VersionProviderInterface::class);
        $this->sut = new CacheBusterVersionStrategy($this->versionProvider);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CacheBusterVersionStrategy::class, $this->sut);
    }

    public function test_it_returns_the_pim_patch_version(): void
    {
        $this->versionProvider->method('getPatch')->willReturn('2.0.1');
        $this->assertSame('2.0.1', $this->sut->getVersion(''));
    }

    public function test_it_returns_the_versioned_asset_path(): void
    {
        $this->versionProvider->method('getPatch')->willReturn('2.0.2');
        $hash = md5('2.0.2');
        $this->assertSame('css/pim.css?' . $hash, $this->sut->applyVersion('css/pim.css'));
    }

    public function test_it_returns_the_versioned_asset_path_with_leading_slash(): void
    {
        $this->versionProvider->method('getPatch')->willReturn('1.7.8');
        $hash = md5('1.7.8');
        $this->assertSame('/js/main.dist.js?' . $hash, $this->sut->applyVersion('/js/main.dist.js'));
    }
}

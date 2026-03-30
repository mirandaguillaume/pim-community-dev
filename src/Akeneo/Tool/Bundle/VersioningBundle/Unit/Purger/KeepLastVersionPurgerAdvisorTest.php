<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetAllButLastVersionIdsByIdsQuery;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\KeepLastVersionPurgerAdvisor;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\PurgeableVersionList;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class KeepLastVersionPurgerAdvisorTest extends TestCase
{
    private SqlGetAllButLastVersionIdsByIdsQuery|MockObject $sqlGetAllButLastVersionIdsByIdsQuery;
    private KeepLastVersionPurgerAdvisor $sut;

    protected function setUp(): void
    {
        $this->sqlGetAllButLastVersionIdsByIdsQuery = $this->createMock(SqlGetAllButLastVersionIdsByIdsQuery::class);
        $this->sut = new KeepLastVersionPurgerAdvisor($this->sqlGetAllButLastVersionIdsByIdsQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(KeepLastVersionPurgerAdvisor::class, $this->sut);
    }

    public function test_it_implements_purger_interface(): void
    {
        $this->assertInstanceOf(VersionPurgerAdvisorInterface::class, $this->sut);
    }

    public function test_it_supports_versions_types_only(): void
    {
        $versionList = new PurgeableVersionList('resource_name', [111, 666]);
        $this->assertSame(true, $this->sut->supports($versionList));
    }

    public function test_it_advises_to_not_purge_the_last_version(): void
    {
        $versionList = new PurgeableVersionList('resource_name', [1, 2, 3, 4]);
        $this->sqlGetAllButLastVersionIdsByIdsQuery->method('execute')->with([1, 2, 3, 4])->willReturn([1, 2, 3]);
        $this->assertEquals(new PurgeableVersionList('resource_name', [1, 2, 3]), $this->sut->isPurgeable($versionList));
    }

    public function test_it_returns_no_version_when_all_are_last_version(): void
    {
        $versionList = new PurgeableVersionList('resource_name', [1, 2, 3, 4]);
        $this->sqlGetAllButLastVersionIdsByIdsQuery->method('execute')->with([1, 2, 3, 4])->willReturn([]);
        $this->assertEquals(new PurgeableVersionList('resource_name', []), $this->sut->isPurgeable($versionList));
    }
}

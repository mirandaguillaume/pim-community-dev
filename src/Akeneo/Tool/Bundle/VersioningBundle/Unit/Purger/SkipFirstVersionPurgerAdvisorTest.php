<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetFirstVersionIdsByIdsQuery;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\PurgeableVersionList;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\SkipFirstVersionPurgerAdvisor;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SkipFirstVersionPurgerAdvisorTest extends TestCase
{
    private SqlGetFirstVersionIdsByIdsQuery|MockObject $sqlGetFirstVersionIdsByIdsQuery;
    private SkipFirstVersionPurgerAdvisor $sut;

    protected function setUp(): void
    {
        $this->sqlGetFirstVersionIdsByIdsQuery = $this->createMock(SqlGetFirstVersionIdsByIdsQuery::class);
        $this->sut = new SkipFirstVersionPurgerAdvisor($this->sqlGetFirstVersionIdsByIdsQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SkipFirstVersionPurgerAdvisor::class, $this->sut);
    }

    public function test_it_is_a_version_purger_advisor(): void
    {
        $this->assertInstanceOf(VersionPurgerAdvisorInterface::class, $this->sut);
    }

    public function test_it_supports_versions_types_only(): void
    {
        $versionList = new PurgeableVersionList('resource_name', [111, 666]);
        $this->assertSame(true, $this->sut->supports($versionList));
    }

    public function test_it_advises_to_not_purge_the_first_version(): void
    {
        $versionList = new PurgeableVersionList('resource_name', [111, 222]);
        $this->sqlGetFirstVersionIdsByIdsQuery->expects($this->once())->method('execute')->with([111, 222])->willReturn([111]);
        $this->assertEquals(new PurgeableVersionList('resource_name', [222]), $this->sut->isPurgeable($versionList));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Versioning\Model;

use Akeneo\Tool\Component\Versioning\Model\Version;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class VersionTest extends TestCase
{
    private Version $sut;

    protected function setUp(): void
    {
        $this->sut = new Version('JobInstance', 1537, null, 'Julia', 'import');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Version::class, $this->sut);
    }

    public function test_it_has_an_id(): void
    {
        $this->assertNull($this->sut->getId());
        $this->sut->setId(1);
        $this->assertSame(1, $this->sut->getId());
    }

    public function test_it_has_an_author(): void
    {
        $this->assertSame('Julia', $this->sut->getAuthor());
    }

    public function test_it_has_a_resource_id(): void
    {
        $this->assertSame(1537, $this->sut->getResourceId());
    }

    public function test_it_has_no_resource_uuid(): void
    {
        $this->assertNull($this->sut->getResourceUuid());
    }

    public function test_it_can_be_constructed_with_a_uuid(): void
    {
        $uuid = Uuid::fromString('114c9108-444d-408a-ab43-195068166d2c');
        $this->sut = new Version('JobInstance', null, $uuid, 'Julia', 'import');
        $this->assertNull($this->sut->getResourceId());
        $this->assertSame($uuid, $this->sut->getResourceUuid());
    }

    public function test_it_has_a_resource_name(): void
    {
        $this->assertSame('JobInstance', $this->sut->getResourceName());
    }

    public function test_it_has_a_version(): void
    {
        $this->assertNull($this->sut->getVersion());
        $this->sut->setVersion(1);
        $this->assertSame(1, $this->sut->getVersion());
    }

    public function test_it_has_a_snapshot(): void
    {
        $this->assertNull($this->sut->getSnapshot());
        $this->sut->setSnapshot(['field' => 'foo']);
        $this->assertSame(['field' => 'foo'], $this->sut->getSnapshot());
    }

    public function test_it_has_a_changeset(): void
    {
        $this->assertSame([], $this->sut->getChangeset());
        $this->sut->setChangeset(['field' => 'foo']);
        $this->assertSame(['field' => 'foo'], $this->sut->getChangeset());
    }

    public function test_it_has_a_context(): void
    {
        $this->assertSame('import', $this->sut->getContext());
    }

    public function test_it_stores_date_of_getting_logged(): void
    {
        $this->assertInstanceOf(\DateTime::class, $this->sut->getLoggedAt());
    }

    public function test_it_can_have_a_pending_state(): void
    {
        $this->assertSame(true, $this->sut->isPending());
        $this->sut->setSnapshot(['field' => 'foo']);
        $this->assertSame(false, $this->sut->isPending());
    }
}

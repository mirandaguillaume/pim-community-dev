<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator\NoneStorageHydrator;
use PHPUnit\Framework\TestCase;

class NoneStorageHydratorTest extends TestCase
{
    private NoneStorageHydrator $sut;

    protected function setUp(): void
    {
        $this->sut = new NoneStorageHydrator();
    }

    public function test_it_supports_only_none_storage(): void
    {
        $this->assertSame(true, $this->sut->supports(['type' => 'none']));
        $this->assertSame(false, $this->sut->supports(['type' => 'local']));
        $this->assertSame(false, $this->sut->supports(['type' => 'unknown']));
    }

    public function test_it_returns_null(): void
    {
        $this->assertEquals(new NoneStorage(), $this->sut->hydrate(['type' => 'none']));
    }
}

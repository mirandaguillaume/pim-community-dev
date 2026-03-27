<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator\LocalStorageHydrator;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PHPUnit\Framework\TestCase;

class LocalStorageHydratorTest extends TestCase
{
    private LocalStorageHydrator $sut;

    protected function setUp(): void
    {
        $this->sut = new LocalStorageHydrator();
    }

    public function test_it_supports_only_local_storage(): void
    {
        $this->assertSame(true, $this->sut->supports(['type' => 'local', 'file_path' => 'a_file_path']));
        $this->assertSame(false, $this->sut->supports(['type' => 'none']));
        $this->assertSame(false, $this->sut->supports(['type' => 'unknown']));
    }

    public function test_it_hydrates_a_local_storage(): void
    {
        $this->assertEquals(new LocalStorage('a_file_path'), $this->sut->hydrate(['type' => 'local', 'file_path' => 'a_file_path']));
    }
}

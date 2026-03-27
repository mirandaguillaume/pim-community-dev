<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Infrastructure\Hydrator;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualUploadStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Hydrator\ManualUploadStorageHydrator;
use PHPUnit\Framework\TestCase;

class ManualUploadStorageHydratorTest extends TestCase
{
    private ManualUploadStorageHydrator $sut;

    protected function setUp(): void
    {
        $this->sut = new ManualUploadStorageHydrator();
    }

    public function test_it_supports_only_manual_upload_storage(): void
    {
        $this->assertSame(true, $this->sut->supports(['type' => 'manual_upload', 'file_path' => 'a_file_path']));
        $this->assertSame(false, $this->sut->supports(['type' => 'none']));
        $this->assertSame(false, $this->sut->supports(['type' => 'unknown']));
    }

    public function test_it_hydrates_a_manual_upload_storage(): void
    {
        $this->assertEquals(new ManualUploadStorage('a_file_path'), $this->sut->hydrate(['type' => 'manual_upload', 'file_path' => 'a_file_path']));
    }
}

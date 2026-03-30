<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Unit\Application\Command\PurgeOrphanCategoryImageFiles;

use Akeneo\Category\Application\Command\PurgeOrphanCategoryImageFiles\PurgeOrphanCategoryImageFilesCommand;
use Akeneo\Category\Application\Command\PurgeOrphanCategoryImageFiles\PurgeOrphanCategoryImageFilesCommandHandler;
use Akeneo\Category\Domain\DTO\IteratorStatus;
use Akeneo\Category\Domain\ImageFile\DeleteCategoryImageFile;
use Akeneo\Category\Domain\ImageFile\GetOrphanCategoryImageFilePaths;
use Akeneo\Category\Domain\ImageFile\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeOrphanCategoryImageFilesCommandHandlerTest extends TestCase
{
    public function testItPurgesOrphanCategoryImageFiles(): void
    {
        $fileSystem = $this->createMock(FilesystemOperator::class);
        $fileSystemProvider = $this->createMock(FilesystemProvider::class);
        $fileSystemProvider
            ->method('getFilesystem')
            ->with(Storage::CATEGORY_STORAGE_ALIAS)
            ->willReturn($fileSystem);

        $getOrphanCategoryImageFilePaths = $this->createMock(GetOrphanCategoryImageFilePaths::class);
        $getOrphanCategoryImageFilePaths
            ->method('__invoke')
            ->willReturn(new \ArrayIterator([
                IteratorStatus::inProgress(),
                IteratorStatus::inProgress(),
                IteratorStatus::done(['a_category/file1.jpg', 'a_category/file2.jpg']),
            ]));

        $deletedPaths = [];
        $deleteCategoryImageFile = $this->createMock(DeleteCategoryImageFile::class);
        $deleteCategoryImageFile
            ->method('__invoke')
            ->willReturnCallback(function (string $path) use (&$deletedPaths): void {
                $deletedPaths[] = $path;
            });

        $handler = new PurgeOrphanCategoryImageFilesCommandHandler(
            $fileSystemProvider,
            $getOrphanCategoryImageFilePaths,
            $deleteCategoryImageFile,
        );

        $purgeOrphanCategoryImageFilesCommand = $this->createMock(PurgeOrphanCategoryImageFilesCommand::class);
        $results = iterator_to_array($handler($purgeOrphanCategoryImageFilesCommand));

        $this->assertCount(5, $results);
        $this->assertEquals([
            IteratorStatus::inProgress(),
            IteratorStatus::inProgress(),
            IteratorStatus::inProgress(),
            IteratorStatus::inProgress(),
            IteratorStatus::done(),
        ], $results);
        $this->assertSame(['a_category/file1.jpg', 'a_category/file2.jpg'], $deletedPaths);
    }

    public function testItSkipsRecentlyModifiedFiles(): void
    {
        $fileSystem = $this->createMock(FilesystemOperator::class);
        $fileSystemProvider = $this->createMock(FilesystemProvider::class);
        $fileSystemProvider
            ->method('getFilesystem')
            ->with(Storage::CATEGORY_STORAGE_ALIAS)
            ->willReturn($fileSystem);

        $getOrphanCategoryImageFilePaths = $this->createMock(GetOrphanCategoryImageFilePaths::class);
        $getOrphanCategoryImageFilePaths
            ->method('__invoke')
            ->willReturn(new \ArrayIterator([
                IteratorStatus::done(['a_category/recent.jpg', 'a_category/old.jpg']),
            ]));

        // recent.jpg: exists and was modified recently (within the last day) -> should be skipped
        // old.jpg: exists but was modified long ago -> should be deleted
        $fileSystem->method('fileExists')->willReturn(true);
        $fileSystem->method('lastModified')->willReturnCallback(
            static function (string $path): int {
                if ($path === 'a_category/recent.jpg') {
                    return time(); // just now, within 1 day threshold
                }

                return time() - 200000; // more than 1 day ago
            },
        );

        $deletedPaths = [];
        $deleteCategoryImageFile = $this->createMock(DeleteCategoryImageFile::class);
        $deleteCategoryImageFile
            ->method('__invoke')
            ->willReturnCallback(function (string $path) use (&$deletedPaths): void {
                $deletedPaths[] = $path;
            });

        $handler = new PurgeOrphanCategoryImageFilesCommandHandler(
            $fileSystemProvider,
            $getOrphanCategoryImageFilePaths,
            $deleteCategoryImageFile,
        );

        $purgeOrphanCategoryImageFilesCommand = $this->createMock(PurgeOrphanCategoryImageFilesCommand::class);
        $results = iterator_to_array($handler($purgeOrphanCategoryImageFilesCommand));

        // Only old.jpg should have been deleted, recent.jpg should have been skipped
        $this->assertSame(['a_category/old.jpg'], $deletedPaths);
    }

    public function testItDeletesNonExistingFiles(): void
    {
        $fileSystem = $this->createMock(FilesystemOperator::class);
        $fileSystemProvider = $this->createMock(FilesystemProvider::class);
        $fileSystemProvider
            ->method('getFilesystem')
            ->with(Storage::CATEGORY_STORAGE_ALIAS)
            ->willReturn($fileSystem);

        $getOrphanCategoryImageFilePaths = $this->createMock(GetOrphanCategoryImageFilePaths::class);
        $getOrphanCategoryImageFilePaths
            ->method('__invoke')
            ->willReturn(new \ArrayIterator([
                IteratorStatus::done(['a_category/missing.jpg']),
            ]));

        // File does not exist on the filesystem -> the condition `fileExists && lastModified > ...` is false
        // so it should proceed to deletion
        $fileSystem->method('fileExists')->willReturn(false);

        $deletedPaths = [];
        $deleteCategoryImageFile = $this->createMock(DeleteCategoryImageFile::class);
        $deleteCategoryImageFile
            ->method('__invoke')
            ->willReturnCallback(function (string $path) use (&$deletedPaths): void {
                $deletedPaths[] = $path;
            });

        $handler = new PurgeOrphanCategoryImageFilesCommandHandler(
            $fileSystemProvider,
            $getOrphanCategoryImageFilePaths,
            $deleteCategoryImageFile,
        );

        $purgeOrphanCategoryImageFilesCommand = $this->createMock(PurgeOrphanCategoryImageFilesCommand::class);
        $results = iterator_to_array($handler($purgeOrphanCategoryImageFilesCommand));

        $this->assertSame(['a_category/missing.jpg'], $deletedPaths);
    }
}

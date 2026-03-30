<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\Category\OnSave;

use Akeneo\Channel\Infrastructure\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnSave\ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTree;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTreeTest extends TestCase
{
    private ObjectRepository|MockObject $jobInstanceRepository;
    private ObjectUpdaterInterface|MockObject $jobInstanceUpdater;
    private BulkSaverInterface|MockObject $jobInstanceSaver;
    private ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTree $sut;

    protected function setUp(): void
    {
        $this->jobInstanceRepository = $this->createMock(ObjectRepository::class);
        $this->jobInstanceUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->jobInstanceSaver = $this->createMock(BulkSaverInterface::class);
        $this->sut = new ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTree($this->jobInstanceRepository,
            $this->jobInstanceUpdater,
            $this->jobInstanceSaver,
            self::supportedJobNames);
    }

}

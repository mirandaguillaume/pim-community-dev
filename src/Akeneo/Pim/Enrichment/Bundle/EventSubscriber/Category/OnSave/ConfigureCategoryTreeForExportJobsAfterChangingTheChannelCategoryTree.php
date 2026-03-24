<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnSave;

use Akeneo\Channel\Infrastructure\Component\Event\ChannelCategoryHasBeenUpdated;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[AsEventListener(event: ChannelCategoryHasBeenUpdated::class, method: 'onChannelCategoryHasBeenUpdatedEvent')]
final readonly class ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTree
{

    /**
     * @param array|string[] $supportedJobNames
     */
    public function __construct(private ObjectRepository $jobInstanceRepository, private ObjectUpdaterInterface $jobInstanceUpdater, private BulkSaverInterface $jobInstanceSaver, private array $supportedJobNames)
    {
    }

    public function onChannelCategoryHasBeenUpdatedEvent(ChannelCategoryHasBeenUpdated $event): void
    {
        $this->updateExports($event->channelCode(), $event->newCategoryCode());
    }

    private function updateExports(string $channelCode, string $categoryCode): void
    {
        $jobInstances = $this->findJobInstancesByChannel($channelCode);

        foreach ($jobInstances as $jobInstance) {
            $parameters = $jobInstance->getRawParameters();
            $parameters = $this->replaceCategoriesFilter($parameters, $categoryCode);

            $this->jobInstanceUpdater->update($jobInstance, ['configuration' => $parameters]);
        }

        $this->jobInstanceSaver->saveAll($jobInstances);
    }

    /**
     * @return JobInstance[]
     */
    private function findJobInstancesByChannel(string $channelCode): array
    {
        $jobInstances = $this->jobInstanceRepository->findBy(
            [
                'jobName' => $this->supportedJobNames,
            ]
        );

        return \array_filter(
            $jobInstances,
            fn (JobInstance $jobInstance): bool => $jobInstance->getRawParameters()['filters']['structure']['scope'] === $channelCode
        );
    }

    private function replaceCategoriesFilter(array $parameters, string $categoryCode): array
    {
        $parameters['filters']['data'] = \array_map(
            function (array $data) use ($categoryCode): array {
                if ($data['field'] === 'categories') {
                    return [
                        'field' => 'categories',
                        'operator' => $data['operator'],
                        'value' => [$categoryCode],
                    ];
                }

                return $data;
            },
            $parameters['filters']['data']
        );

        return $parameters;
    }
}

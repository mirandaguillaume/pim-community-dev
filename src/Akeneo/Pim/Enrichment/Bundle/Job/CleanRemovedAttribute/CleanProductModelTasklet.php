<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Job\CleanRemovedAttribute;

use Akeneo\Pim\Enrichment\Bundle\Product\RemoveValuesFromProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelIdentifiersWithRemovedAttributeInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class CleanProductModelTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const BATCH_SIZE = 100;

    private StepExecution $stepExecution;

    public function __construct(private readonly GetProductModelIdentifiersWithRemovedAttributeInterface $getProductModelsIdentifiersWithRemovedAttribute, private readonly CountProductModelsWithRemovedAttributeInterface $countProductModelsWithRemovedAttribute, private readonly RemoveValuesFromProductModels $removeValuesFromProductModels, private readonly JobRepositoryInterface $jobRepository) {}

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function isTrackable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $attributeCodes = $this->stepExecution->getJobParameters()->get('attribute_codes');

        $totalProductModelCount = $this->countProductModelsWithRemovedAttribute->count($attributeCodes);
        $this->stepExecution->setTotalItems($totalProductModelCount);
        $this->stepExecution->incrementSummaryInfo('read', $totalProductModelCount);

        foreach ($this->getProductModelsIdentifiersWithRemovedAttribute->nextBatch(
            $attributeCodes,
            self::BATCH_SIZE
        ) as $identifiers) {
            $this->removeValuesFromProductModels->forAttributeCodes($attributeCodes, $identifiers);
            $productModelCount = is_countable($identifiers) ? count($identifiers) : 0;
            $this->stepExecution->incrementSummaryInfo('process', $productModelCount);
            $this->stepExecution->incrementProcessedItems($productModelCount);
            $this->jobRepository->updateStepExecution($this->stepExecution);
        }
    }
}

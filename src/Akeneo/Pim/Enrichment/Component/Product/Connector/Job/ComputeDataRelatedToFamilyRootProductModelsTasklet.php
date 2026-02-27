<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * For each line of the file of families to import we will:
 * - fetch the corresponding family object,
 * - fetch all the root product models of this family,
 * - batch save these product models
 *
 * This way, on family import, the family's root product models data will be
 * computed and all family variant's corresponding attributes will be indexed.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeDataRelatedToFamilyRootProductModelsTasklet implements TaskletInterface, InitializableInterface, TrackableTaskletInterface
{
    private ?\Akeneo\Tool\Component\Batch\Model\StepExecution $stepExecution = null;

    public function __construct(private readonly IdentifiableObjectRepositoryInterface $familyRepository, private readonly ProductQueryBuilderFactoryInterface $queryBuilderFactory, private readonly ItemReaderInterface $familyReader, private readonly KeepOnlyValuesForVariation $keepOnlyValuesForVariation, private readonly ValidatorInterface $validator, private readonly BulkSaverInterface $productModelSaver, private readonly EntityManagerClearerInterface $cacheClearer, private readonly JobRepositoryInterface $jobRepository, private readonly int $batchSize)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->initialize();
        $familyCodes = $this->extractFamilyCodes();
        if (empty($familyCodes)) {
            return;
        }

        $productModels = $this->getRootProductModelsForFamily($familyCodes);
        $this->stepExecution->setTotalItems($productModels->count());

        $skippedProductModels = [];
        $productModelsToSave = [];
        foreach ($productModels as $productModel) {
            $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant([$productModel]);

            if (!$this->isValid($productModel)) {
                $this->stepExecution->incrementSummaryInfo('skip');
                $this->stepExecution->incrementProcessedItems(1);

                $skippedProductModels[] = $productModel;
            } else {
                $productModelsToSave[] = $productModel;
            }

            if (0 === (count($productModelsToSave) + count($skippedProductModels)) % $this->batchSize) {
                $this->saveProductsModel($productModelsToSave);
                $productModelsToSave = [];
                $skippedProductModels = [];
                $this->cacheClearer->clear();
            }
        }

        $this->saveProductsModel($productModelsToSave);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->cacheClearer->clear();

        if ($this->familyReader instanceof InitializableInterface) {
            $this->familyReader->initialize();
        }
    }

    private function isValid(EntityWithFamilyVariantInterface $entityWithFamilyVariant): bool
    {
        $violations = $this->validator->validate($entityWithFamilyVariant);

        return $violations->count() === 0;
    }

    private function saveProductsModel(array $productModels): void
    {
        if (empty($productModels)) {
            return;
        }

        $this->productModelSaver->saveAll($productModels);
        $this->stepExecution->incrementSummaryInfo('process', count($productModels));
        $this->stepExecution->incrementProcessedItems(count($productModels));
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    private function extractFamilyCodes(): array
    {
        $familyCodes = [];

        while (true) {
            try {
                $familyItem = $this->familyReader->read();
                if (null === $familyItem) {
                    break;
                }
            } catch (InvalidItemException) {
                continue;
            }

            $family = $this->familyRepository->findOneByIdentifier($familyItem['code']);
            if (null === $family) {
                $this->stepExecution->incrementSummaryInfo('skip');

                continue;
            }

            $familyCodes[] = $family->getCode();
        }

        return $familyCodes;
    }

    private function getRootProductModelsForFamily(array $familyCodes): CursorInterface
    {
        $pqb = $this->queryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, $familyCodes);
        $pqb->addFilter('parent', Operators::IS_EMPTY, null);

        return $pqb->execute();
    }

    public function isTrackable(): bool
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Structure\Component\AttributeGroup\Query\FindAttributeGroupOrdersEqualOrSuperiorTo;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * For each attribute group imported by file, we will check if its sort order
 * is in conflict with an existing attribute group.
 *
 * In case of conflict, a new available sort order will be given to it.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnsureConsistentAttributeGroupOrderTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?\Akeneo\Tool\Component\Batch\Model\StepExecution $stepExecution = null;

    public function __construct(private readonly IdentifiableObjectRepositoryInterface $attributeGroupRepository, private readonly ItemReaderInterface $attributeGroupReader, private readonly SaverInterface $attributeGroupSaver, private readonly FindAttributeGroupOrdersEqualOrSuperiorTo $findAttributeGroupOrdersEqualOrSuperiorTo, private readonly ValidatorInterface $validator, private readonly JobRepositoryInterface $jobRepository) {}

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
        if ($this->attributeGroupReader instanceof InitializableInterface) {
            $this->attributeGroupReader->initialize();
        }

        if ($this->attributeGroupReader instanceof TrackableItemReaderInterface) {
            $this->stepExecution->setTotalItems($this->attributeGroupReader->totalItems());
        }

        while (true) {
            try {
                $attributeGroupItem = $this->attributeGroupReader->read();

                if (null === $attributeGroupItem) {
                    break;
                }
            } catch (InvalidItemException) {
                continue;
            }

            /** @var AttributeGroupInterface $attributeGroup */
            $attributeGroup = $this->attributeGroupRepository->findOneByIdentifier($attributeGroupItem['code']);

            if (null === $attributeGroup) {
                $this->updateProgressWithSkipped();

                continue;
            }

            $ordersEqualsOrSuperior = $this->findAttributeGroupOrdersEqualOrSuperiorTo->execute($attributeGroup);

            // If there is a conflict in sort order, set the next one available
            if (!empty($ordersEqualsOrSuperior) && (int) current($ordersEqualsOrSuperior) === (int) $attributeGroup->getSortOrder()) {
                $rangeOrders = range(min($ordersEqualsOrSuperior), max($ordersEqualsOrSuperior));
                $availableOrders = array_diff($rangeOrders, $ordersEqualsOrSuperior);

                if (!empty($availableOrders)) {
                    $nextAvailableOrder = current($availableOrders);
                } else {
                    $nextAvailableOrder = max($ordersEqualsOrSuperior) + 1;
                }

                $attributeGroup->setSortOrder($nextAvailableOrder);
                $violations = $this->validator->validate($attributeGroup);

                if ($violations->count() > 0) {
                    $this->updateProgressWithSkipped();

                    continue;
                }

                $this->attributeGroupSaver->save($attributeGroup);
                $this->updateProgressWithProcessed();
            } else {
                $this->updateProgressWithSkipped();
            }
        }
    }

    private function updateProgressWithSkipped(): void
    {
        $this->stepExecution->incrementSummaryInfo('skip');
        $this->stepExecution->incrementProcessedItems();
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    private function updateProgressWithProcessed(): void
    {
        $this->stepExecution->incrementSummaryInfo('process');
        $this->stepExecution->incrementProcessedItems();
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    public function isTrackable(): bool
    {
        return $this->attributeGroupReader instanceof TrackableItemReaderInterface;
    }
}

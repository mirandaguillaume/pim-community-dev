<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * Product and product model writer for mass edit
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelWriter implements ItemWriterInterface, StepExecutionAwareInterface, InitializableInterface
{
    protected \Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager $versionManager;

    /** @var StepExecution */
    protected $stepExecution;

    protected \Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface $productSaver;

    protected \Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface $productModelSaver;

    public function __construct(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver
    ) {
        $this->versionManager = $versionManager;
        $this->productSaver = $productSaver;
        $this->productModelSaver = $productModelSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        $products = array_filter($items, fn ($item): bool => $item instanceof ProductInterface);
        $productModels = array_filter($items, fn ($item): bool => $item instanceof ProductModelInterface);

        array_walk($items, function (\Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface $item): void {
            $this->incrementCount($item);
        });

        $this->productSaver->saveAll($products);
        $this->productModelSaver->saveAll($productModels);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $realTimeVersioning = $jobParameters->get('realTimeVersioning');
        $this->versionManager->setRealTimeVersioning($realTimeVersioning);
    }

    protected function incrementCount(EntityWithFamilyInterface $entity)
    {
        if (!$entity->isNew()) {
            $this->stepExecution->incrementSummaryInfo('update');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}

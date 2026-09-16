<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Writer\Database\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\MassEdit\ProductAndProductModelWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Writer of the mass edit jobs, edit_common_attributes included. The job parameter realTimeVersioning decides whether
 * each saved product gets its version, with its changeset, during the job: that is what the product history showed
 * after a mass edit in mass_edit_and_update_attribute_history.feature:40 (PIM-1920).
 * EditCommonAttributesVersioningIntegration checks the resulting versions end to end.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelWriterTest extends TestCase
{
    private VersionManager|MockObject $versionManager;
    private BulkSaverInterface|MockObject $productSaver;
    private BulkSaverInterface|MockObject $productModelSaver;
    private StepExecution|MockObject $stepExecution;
    private JobParameters|MockObject $jobParameters;
    private ProductAndProductModelWriter $sut;

    protected function setUp(): void
    {
        $this->versionManager = $this->createMock(VersionManager::class);
        $this->productSaver = $this->createMock(BulkSaverInterface::class);
        $this->productModelSaver = $this->createMock(BulkSaverInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->jobParameters = $this->createMock(JobParameters::class);
        $this->stepExecution->method('getJobParameters')->willReturn($this->jobParameters);

        $this->sut = new ProductAndProductModelWriter(
            $this->versionManager,
            $this->productSaver,
            $this->productModelSaver,
        );
        $this->sut->setStepExecution($this->stepExecution);
    }

    public function test_initialize_turns_real_time_versioning_on_when_the_job_asks_for_it(): void
    {
        $this->jobParameters->method('get')->with('realTimeVersioning')->willReturn(true);
        $this->versionManager->expects($this->once())->method('setRealTimeVersioning')->with(true);

        $this->sut->initialize();
    }

    public function test_initialize_turns_real_time_versioning_off_when_the_job_asks_for_it(): void
    {
        $this->jobParameters->method('get')->with('realTimeVersioning')->willReturn(false);
        $this->versionManager->expects($this->once())->method('setRealTimeVersioning')->with(false);

        $this->sut->initialize();
    }

    public function test_write_saves_products_and_product_models_with_their_own_saver_and_counts_them(): void
    {
        $existingProduct = $this->entity(ProductInterface::class, false);
        $newProductModel = $this->entity(ProductModelInterface::class, true);
        $otherExistingProduct = $this->entity(ProductInterface::class, false);

        $this->productSaver->expects($this->once())
            ->method('saveAll')
            ->with($this->callback(
                static fn (array $products): bool => $products === [0 => $existingProduct, 2 => $otherExistingProduct]
            ));
        $this->productModelSaver->expects($this->once())
            ->method('saveAll')
            ->with($this->callback(static fn (array $productModels): bool => $productModels === [1 => $newProductModel]));

        $summary = [];
        $this->stepExecution->expects($this->exactly(3))
            ->method('incrementSummaryInfo')
            ->willReturnCallback(function (string $key) use (&$summary): void {
                $summary[] = $key;
            });

        $this->sut->write([$existingProduct, $newProductModel, $otherExistingProduct]);

        $this->assertSame(['update', 'create', 'update'], $summary);
    }

    /**
     * @param class-string<ProductInterface|ProductModelInterface> $class
     */
    private function entity(string $class, bool $isNew): ProductInterface|ProductModelInterface|MockObject
    {
        $entity = $this->createMock($class);
        $entity->method('isNew')->willReturn($isNew);

        return $entity;
    }
}

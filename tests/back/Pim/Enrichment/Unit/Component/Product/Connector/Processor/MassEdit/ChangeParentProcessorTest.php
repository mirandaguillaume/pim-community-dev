<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\ChangeParentProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangeParentProcessorTest extends TestCase
{
    private ValidatorInterface|MockObject $productValidator;
    private ValidatorInterface|MockObject $productModelValidator;
    private ObjectUpdaterInterface|MockObject $productUpdater;
    private ObjectUpdaterInterface|MockObject $productModelUpdater;
    private ChangeParentProcessor $sut;

    protected function setUp(): void
    {
        $this->productValidator = $this->createMock(ValidatorInterface::class);
        $this->productModelValidator = $this->createMock(ValidatorInterface::class);
        $this->productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->productModelUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new ChangeParentProcessor(
            $this->productValidator,
            $this->productModelValidator,
            $this->productUpdater,
            $this->productModelUpdater,
        );
    }

    public function test_it_throws_an_exception_if_product_is_not_a_correct_type(): void
    {
        $this->expectException(InvalidObjectException::class);
        $stepExecution = $this->createStepExecution([['value' => '42']]);
        $this->sut->setStepExecution($stepExecution);
        $this->sut->process(new \stdClass());
    }

    public function test_it_changes_the_parent_of_a_variant_product(): void
    {
        $product = $this->createMock(EntityWithFamilyVariantInterface::class);
        $stepExecution = $this->createStepExecution([['value' => '42']]);
        $this->sut->setStepExecution($stepExecution);

        $this->productUpdater->expects($this->once())->method('update')->with($product, ['parent' => '42']);
        $violations = new ConstraintViolationList([]);
        $this->productValidator->method('validate')->with($product)->willReturn($violations);

        $this->assertSame($product, $this->sut->process($product));
    }

    public function test_it_fails_to_update_an_invalid_product(): void
    {
        $product = $this->createMock(EntityWithFamilyVariantInterface::class);
        $stepExecution = $this->createStepExecution([['value' => '42']]);
        $this->sut->setStepExecution($stepExecution);

        $this->productUpdater->expects($this->once())->method('update')->with($product, ['parent' => '42']);
        $violation = new ConstraintViolation('error1', '', [], '', '', '');
        $violations = new ConstraintViolationList([$violation]);
        $this->productValidator->method('validate')->with($product)->willReturn($violations);
        $stepExecution->expects($this->once())->method('addWarning');

        $this->assertNull($this->sut->process($product));
    }

    public function test_it_adds_a_warning_message_if_the_updater_fails(): void
    {
        $product = $this->createMock(EntityWithFamilyVariantInterface::class);
        $stepExecution = $this->createStepExecution([['value' => '42']]);
        $this->sut->setStepExecution($stepExecution);

        $this->productUpdater->method('update')->willThrowException(
            InvalidPropertyException::expected('parent', 'string', ChangeParentProcessor::class)
        );
        $stepExecution->expects($this->once())->method('addWarning');

        $this->assertNull($this->sut->process($product));
    }

    private function createStepExecution(array $actions): StepExecution|MockObject
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('get')->with('actions')->willReturn($actions);

        return $stepExecution;
    }
}

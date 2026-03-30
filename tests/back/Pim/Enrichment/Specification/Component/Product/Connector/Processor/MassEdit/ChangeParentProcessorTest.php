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
    private ChangeParentProcessor $sut;

    protected function setUp(): void
    {
        $this->sut = new ChangeParentProcessor();
    }

    public function test_it_throws_an_exception_if_product_is_not_a_correct_type(): void
    {
        $this->sut->shouldThrow(InvalidObjectException::class)->duringProcess(new \stdClass());
    }

    public function test_it_changes_the_parent_of_a_variant_product(): void
    {
        $product = $this->createMock(EntityWithFamilyVariantInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $jobParameters->method('get')->with('actions')->willReturn([['value' => '42']]);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $violations = new ConstraintViolationList([]);
        $productValidator->validate($product)->willReturn($violations);
        $productUpdater->update($product, ['parent' => '42'])->shouldBeCalled();
        $this->assertSame($product, $this->sut->process($product));
    }

    public function test_it_fails_to_update_an_invalid_product(): void
    {
        $product = $this->createMock(EntityWithFamilyVariantInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $jobParameters->method('get')->with('actions')->willReturn([['value' => '42']]);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $violation = new ConstraintViolation('error1', '', [], '', '', '');
        $violations = new ConstraintViolationList([$violation]);
        $productValidator->validate($product)->willReturn($violations);
        $stepExecution->expects($this->once())->method('addWarning');
        $productUpdater->update($product, ['parent' => '42'])->shouldBeCalled();
        $this->assertNull($this->sut->process($product));
    }

    public function test_it_adds_a_warning_message_if_the_updater_fails(): void
    {
        $product = $this->createMock(EntityWithFamilyVariantInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $jobParameters->method('get')->with('actions')->willReturn([['value' => '42']]);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $stepExecution->expects($this->once())->method('addWarning');
        $productUpdater->update($product, ['parent' => '42'])->willThrow(InvalidPropertyException::class);
        $productValidator->validate($product)->shouldNotBeCalled();
        $this->assertNull($this->sut->process($product));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\MarkEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PrepareEvaluationsParameters;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\MarkEvaluation\MarkProductsEvaluationImpactedByUpdatedAttributeGroupActivationTasklet;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class MarkProductsEvaluationImpactedByUpdatedAttributeGroupActivationTaskletTest extends TestCase
{
    private CreateCriteriaEvaluations|MockObject $createCriteriaEvaluations;
    private GetEntityIdsImpactedByAttributeGroupActivationQueryInterface|MockObject $getProductIdsImpactedByAttributeGroupActivationQuery;
    private LoggerInterface|MockObject $logger;
    private MarkProductsEvaluationImpactedByUpdatedAttributeGroupActivationTasklet $sut;

    protected function setUp(): void
    {
        $this->createCriteriaEvaluations = $this->createMock(CreateCriteriaEvaluations::class);
        $this->getProductIdsImpactedByAttributeGroupActivationQuery = $this->createMock(GetEntityIdsImpactedByAttributeGroupActivationQueryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new MarkProductsEvaluationImpactedByUpdatedAttributeGroupActivationTasklet($this->createCriteriaEvaluations, $this->getProductIdsImpactedByAttributeGroupActivationQuery, $this->logger, 2);
    }

    public function test_it_is_a_tasklet(): void
    {
        $this->assertInstanceOf(TaskletInterface::class, $this->sut);
        $this->assertInstanceOf(MarkProductsEvaluationImpactedByUpdatedAttributeGroupActivationTasklet::class, $this->sut);
    }

    public function test_it_marks_products_evaluation_impacted_by_updated_attribute_group_activation(): void
    {
        $stepExecution = $this->buildStepExecution();
        $this->sut->setStepExecution($stepExecution);
        $productUuids = [
                    ProductUuidCollection::fromStrings(['6d125b99-d971-41d9-a264-b020cd486aee', 'fef37e64-a963-47a9-b087-2cc67968f0a2']),
                    ProductUuidCollection::fromStrings(['df470d52-7723-4890-85a0-e79be625e2ed']),
                ];
        $this->getProductIdsImpactedByAttributeGroupActivationQuery->method('updatedSince')->with($this->callback(fn (\DateTimeImmutable $updatedSince) => $updatedSince->format('Y-m-d H:i:s') === '2023-02-07 14:23:56'), 2)->willReturn(new \ArrayIterator($productUuids));
        $this->createCriteriaEvaluations->expects($this->once())->method('createAll')->with($productUuids[0]);
        $this->createCriteriaEvaluations->expects($this->once())->method('createAll')->with($productUuids[1]);
        $this->logger->expects($this->never())->method('error');
        $this->sut->execute();
        Assert::same($stepExecution->getWriteCount(), 3);
    }

    public function test_it_does_not_interrupt_the_job_if_an_error_occurs(): void
    {
        $this->sut->setStepExecution($this->buildStepExecution());
        $this->getProductIdsImpactedByAttributeGroupActivationQuery->method('updatedSince')->willThrowException(new \Exception('error'));
        $this->logger->expects($this->once())->method('error');
        $this->sut->execute();
    }

    private function buildStepExecution(): StepExecution
    {
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters(new JobParameters([
            PrepareEvaluationsParameters::UPDATED_SINCE_PARAMETER => '2023-02-07 14:23:56',
        ]));
    
        return new StepExecution('mark_products_evaluation_impacted_by_updated_attribute_group_activation', $jobExecution);
    }
}

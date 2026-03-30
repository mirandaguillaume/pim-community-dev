<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandler;
use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstance as JobInstanceConstraint;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PublishJobToQueueTest extends TestCase
{
    private DoctrineJobRepository|MockObject $jobRepository;
    private ValidatorInterface|MockObject $validator;
    private JobExecutionQueueInterface|MockObject $jobExecutionQueue;
    private JobExecutionMessageFactory|MockObject $jobExecutionMessageFactory;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private BatchLogHandler|MockObject $batchLogHandler;
    private CreateJobExecutionHandler|MockObject $createJobExecutionHandler;
    private PublishJobToQueue $sut;

    protected function setUp(): void
    {
        $this->jobRepository = $this->createMock(DoctrineJobRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->jobExecutionQueue = $this->createMock(JobExecutionQueueInterface::class);
        $this->jobExecutionMessageFactory = $this->createMock(JobExecutionMessageFactory::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->batchLogHandler = $this->createMock(BatchLogHandler::class);
        $this->createJobExecutionHandler = $this->createMock(CreateJobExecutionHandler::class);
        $this->sut = new PublishJobToQueue(
            'prod',
            $this->jobRepository,
            $this->validator,
            $this->jobExecutionQueue,
            $this->jobExecutionMessageFactory,
            $this->eventDispatcher,
            $this->batchLogHandler,
            $this->createJobExecutionHandler,
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(PublishJobToQueue::class, $this->sut);
    }

    public function test_it_publishes_a_job_to_the_execution_queue(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $jobInstanceRepository = $this->createMock(EntityRepository::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $jobExecution = $this->createMock(JobExecution::class);

        $batchCode = 'job-code';
        $config = [];
        $this->jobRepository->method('getJobManager')->willReturn($entityManager);
        $entityManager->method('getRepository')->with(JobInstance::class)->willReturn($jobInstanceRepository);
        $jobInstanceRepository->method('findOneBy')->with(['code' => $batchCode])->willReturn($jobInstance);
        $this->createJobExecutionHandler->method('createFromJobInstance')->with($jobInstance, $config, null)->willReturn($jobExecution);
        $jobExecution->method('getId')->willReturn(42);
        $this->batchLogHandler->expects($this->once())->method('setSubDirectory')->with('42');
        $jobExecutionMessage = UiJobExecutionMessage::createJobExecutionMessage(42, []);
        $this->jobExecutionMessageFactory->method('buildFromJobInstance')->with($jobInstance, 42, ['env' => 'prod'])->willReturn($jobExecutionMessage);
        $this->jobExecutionQueue->expects($this->once())->method('publish')->with($jobExecutionMessage);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($this->isInstanceOf(JobExecutionEvent::class), EventInterface::JOB_EXECUTION_CREATED);
        $this->assertSame($jobExecution, $this->sut->publish($batchCode, $config));
    }
}

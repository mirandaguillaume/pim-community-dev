<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\BatchQueueBundle\Command\PublishJobToQueueCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishJobToQueueCommandTest extends TestCase
{
    private PublishJobToQueue|MockObject $publishJobToQueue;
    private DoctrineJobRepository|MockObject $jobRepository;
    private JobRegistry|MockObject $jobRegistry;
    private JobParametersFactory|MockObject $jobParametersFactory;
    private JobInstanceRepository|MockObject $jobInstanceRepository;
    private EntityManagerInterface|MockObject $entityManager;
    private PublishJobToQueueCommand $sut;

    protected function setUp(): void
    {
        $this->publishJobToQueue = $this->createMock(PublishJobToQueue::class);
        $this->jobRepository = $this->createMock(DoctrineJobRepository::class);
        $this->jobRegistry = $this->createMock(JobRegistry::class);
        $this->jobParametersFactory = $this->createMock(JobParametersFactory::class);
        $this->jobInstanceRepository = $this->createMock(JobInstanceRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->sut = new PublishJobToQueueCommand(
            $this->publishJobToQueue,
            $this->jobRepository,
            $this->jobRegistry,
            $this->jobParametersFactory,
            $jobInstanceClass
        );
        $jobInstanceClass = \Akeneo\Tool\Component\Batch\Model\JobInstance::class;
        $this->jobRepository->method('getJobManager')->willReturn($this->entityManager);
        $this->entityManager->method('getRepository')->with($jobInstanceClass)->willReturn($this->jobInstanceRepository);
    }

    public function test_it_has_a_name(): void
    {
        $this->assertSame('akeneo:batch:publish-job-to-queue', $this->sut->getName());
    }

    public function test_it_is_a_command(): void
    {
        $this->assertInstanceOf(Command::class, $this->sut);
    }

    public function test_it_publishes_a_job_to_the_job_queue(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $application = $this->createMock(Application::class);
        $helperSet = $this->createMock(HelperSet::class);
        $definition = $this->createMock(InputDefinition::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $definition->method('getOptions')->willReturn([]);
        $definition->method('getArguments')->willReturn([]);
        $application->method('getHelperSet')->willReturn($helperSet);
        $application->method('getDefinition')->willReturn($definition);
        $this->sut->setApplication($application);
        $input->expects($this->once())->method('bind')->with($this->anything());
        $input->expects($this->once())->method('isInteractive');
        $input->expects($this->once())->method('hasArgument')->with($this->anything());
        $input->expects($this->once())->method('validate');
        $inputCode = 'the_job_instance_code';
        $inputConfig = '{"key": "data", "superkey": 50}';
        $inputNoLog = null;
        $inputUsername = 'admin';
        $inputEmail = null;
        $input->method('getArgument')->with('code')->willReturn($inputCode);
        $input->method('getOption')->with('config')->willReturn($inputConfig);
        $input->method('getOption')->with('no-log')->willReturn($inputNoLog);
        $input->method('getOption')->with('username')->willReturn($inputUsername);
        $input->method('getOption')->with('email')->willReturn($inputEmail);
        $this->publishJobToQueue->expects($this->once())->method('publish')->with(
            $inputCode,
            json_decode($inputConfig, true),
            false,
            $inputUsername,
            $inputEmail
        );
        $this->jobInstanceRepository->method('findOneBy')->with(['code' => $inputCode])->willReturn($jobInstance);
        $jobInstance->method('getType')->willReturn('jobType');
        $jobInstance->method('getCode')->willReturn($inputCode);
        $output->expects($this->once())->method('writeln')->with('<info>JobType the_job_instance_code has been successfully pushed into the queue.</info>');
        $this->sut->run($input, $output);
    }

    public function test_it_throws_an_exception_if_the_config_string_is_malformed(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $application = $this->createMock(Application::class);
        $helperSet = $this->createMock(HelperSet::class);
        $definition = $this->createMock(InputDefinition::class);

        $definition->method('getOptions')->willReturn([]);
        $definition->method('getArguments')->willReturn([]);
        $application->method('getHelperSet')->willReturn($helperSet);
        $application->method('getDefinition')->willReturn($definition);
        $this->sut->setApplication($application);
        $input->expects($this->once())->method('bind')->with($this->anything());
        $input->expects($this->once())->method('isInteractive');
        $input->expects($this->once())->method('hasArgument')->with($this->anything());
        $input->expects($this->once())->method('validate');
        $inputCode = 'the_job_instance_code';
        $inputConfig = '{{invalid_config}';
        $inputNoLog = null;
        $inputUsername = 'admin';
        $inputEmail = null;
        $input->method('getArgument')->with('code')->willReturn($inputCode);
        $input->method('getOption')->with('config')->willReturn($inputConfig);
        $input->method('getOption')->with('no-log')->willReturn($inputNoLog);
        $input->method('getOption')->with('username')->willReturn($inputUsername);
        $input->method('getOption')->with('email')->willReturn($inputEmail);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->run($input, $output);
    }
}

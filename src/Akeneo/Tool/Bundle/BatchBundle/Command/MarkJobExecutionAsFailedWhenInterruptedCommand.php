<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Command;

use Akeneo\Tool\Component\Batch\Query\MarkJobExecutionAsFailedWhenInterrupted;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'akeneo:batch:clean-job-executions', description: 'Mark as failed the job executions that are stuck in status STARTED or STOPPING.')]
class MarkJobExecutionAsFailedWhenInterruptedCommand extends Command
{
    public function __construct(
        private readonly MarkJobExecutionAsFailedWhenInterrupted $markJobExecutionAsFailedWhenInterrupted
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'jobCodes',
                InputArgument::REQUIRED,
                'Job instance codes that need to have job executions to be cleaned. For example: "job_1,job_2".'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobCodes = $input->getArgument('jobCodes');
        $jobCodes =  array_map('trim', explode(',', trim((string) $jobCodes)));

        $impactedRows = $this->markJobExecutionAsFailedWhenInterrupted->execute($jobCodes);
        $output->writeln(sprintf('<info>%s job executions cleaned</info>', $impactedRows));

        return 0;
    }
}

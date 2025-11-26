<?php

namespace Akeneo\Platform\Installer\Infrastructure\FixtureLoader;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Reader\File\Yaml\Reader;
use Symfony\Component\Config\FileLocator;

/**
 * Read the 'fixture_jobs.yml' to build the job instances that can be used to install the PIM.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobInstancesBuilder
{
    /**
     * @param string[] $jobsFilePaths
     */
    public function __construct(protected FileLocator $fileLocator, protected Reader $yamlReader, protected ItemProcessorInterface $jobInstanceProcessor, protected array $jobsFilePaths)
    {
    }

    /**
     * Load the fixture jobs in database.
     *
     * @return JobInstance[]
     */
    /**
     * @return JobInstance[]
     */
    public function build(): array
    {
        $rawJobs = $this->readOrderedRawJobData();

        return $this->buildJobInstances($rawJobs);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function readOrderedRawJobData(): array
    {
        $rawJobs = [];
        $fileLocator = $this->getFileLocator();
        foreach ($this->jobsFilePaths as $jobsFilePath) {
            $yamlReader = $this->getYamlReader();
            $realPath = $fileLocator->locate('@'.$jobsFilePath);
            $jobExecution = new JobExecution();
            $jobParameters = new JobParameters(['storage' => ['type' => LocalStorage::TYPE, 'file_path' => $realPath]]);
            $jobExecution->setJobParameters($jobParameters);
            $stepExecution = new StepExecution('reader', $jobExecution);
            $yamlReader->setStepExecution($stepExecution);

            while ($rawJob = $yamlReader->read()) {
                $rawJobs[] = $rawJob;
            }

            usort(
                $rawJobs,
                fn ($item1, $item2) => $item1['order'] <=> $item2['order'],
            );
        }

        return $rawJobs;
    }

    /**
     * @param array<int, array<string, mixed>> $rawJobs
     *
     * @return JobInstance[]
     */
    protected function buildJobInstances(array $rawJobs): array
    {
        $processor = $this->getJobInstanceProcessor();
        $jobInstances = [];
        foreach ($rawJobs as $rawJob) {
            unset($rawJob['order']);
            $jobInstance = $processor->process($rawJob);
            $jobInstances[] = $jobInstance;
        }

        return $jobInstances;
    }

    protected function getFileLocator(): FileLocator
    {
        return $this->fileLocator;
    }

    protected function getYamlReader(): Reader
    {
        return $this->yamlReader;
    }

    protected function getJobInstanceProcessor(): ItemProcessorInterface
    {
        return $this->jobInstanceProcessor;
    }
}

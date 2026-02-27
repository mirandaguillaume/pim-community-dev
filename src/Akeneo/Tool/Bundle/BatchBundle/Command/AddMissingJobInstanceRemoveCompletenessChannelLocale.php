<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsCommand(name: 'akeneo:batch:add-missing-job-instance-remove-completeness-for-channel-and-locale', description: 'Add missing job instance remove_completeness_for_channel_and_locale')]

class AddMissingJobInstanceRemoveCompletenessChannelLocale extends Command
{
    final public const EXIT_SUCCESS_CODE = 0;
    final public const EXIT_ERROR_CODE = 1;

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {}

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->jobExists('remove_completeness_for_channel_and_locale')) {
            $output->writeln('The "remove_completeness_for_channel_and_locale" job instance already exists');
            return self::EXIT_SUCCESS_CODE;
        }

        $sql = <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type);
            SQL;
        try {
            $this->connection->executeQuery($sql, [
                'code' => 'remove_completeness_for_channel_and_locale',
                'label' => 'Remove completeness for channel and locale',
                'job_name' => 'remove_completeness_for_channel_and_locale',
                'status' => 0,
                'connector' => 'internal',
                'raw_parameters' => 'a:0:{}',
                'type' => 'remove_completeness_for_channel_and_locale',
            ]);

            $output->writeln('The "remove_completeness_for_channel_and_locale" job instance successfully added');
        } catch (\Exception) {
            $output->writeln("Error occurred");
            return self::EXIT_ERROR_CODE;
        }

        return self::EXIT_SUCCESS_CODE;
    }

    private function jobExists(string $jobCode): bool
    {
        $jobInstanceResult = $this->connection->executeQuery(
            'SELECT * FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => $jobCode]
        );

        return 1 <= $jobInstanceResult->rowCount();
    }
}

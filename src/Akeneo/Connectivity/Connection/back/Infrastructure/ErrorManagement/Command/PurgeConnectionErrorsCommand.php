<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\PurgeConnectionErrorsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\SelectAllAuditableConnectionCodeQuery;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsCommand(name: 'akeneo:connectivity-connection:purge-error', description: 'Purge connection errors over 100 and older than a week')]

class PurgeConnectionErrorsCommand extends Command
{
    private const TABLE_NOT_FOUND_ERROR_CODE = '42S02';

    public function __construct(
        private readonly SelectAllAuditableConnectionCodeQuery $selectAllAuditableConnectionCodes,
        private readonly PurgeConnectionErrorsQuery $purgeErrors,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Errors are thrown when the database or ElasticSearch are off following a deployment
        // but the cron is still active and executing this command.
        // We decided to make these errors silent to avoid noise in our alert monitoring
        try {
            $this->logger->info('Start purge connection error');

            $codes = $this->selectAllAuditableConnectionCodes->execute();
            $this->purgeErrors->execute($codes);

            $this->logger->info('End purge connection error');
        } catch (TableNotFoundException $exception) {
            if ($exception->getPrevious()?->getCode() === self::TABLE_NOT_FOUND_ERROR_CODE) {
                $this->logger->warning('Table not found', ['exception' => $exception]);

                return Command::FAILURE;
            }

            throw $exception;
        } catch (Missing404Exception $exception) {
            $this->logger->warning('Elasticsearch is unavailable', ['exception' => $exception]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

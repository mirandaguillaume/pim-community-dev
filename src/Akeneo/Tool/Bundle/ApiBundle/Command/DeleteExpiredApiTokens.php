<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Command;

use Akeneo\Tool\Bundle\ApiBundle\Handler\DeleteExpiredTokensHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pim:oauth-server:delete-expired-tokens', description: 'Deletes all expired tokens (access token, refresh token)')]
class DeleteExpiredApiTokens extends Command
{
    public function __construct(
        private readonly DeleteExpiredTokensHandler $deleteExpiredTokensHandler
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->deleteExpiredTokensHandler->handle();

        return Command::SUCCESS;
    }
}

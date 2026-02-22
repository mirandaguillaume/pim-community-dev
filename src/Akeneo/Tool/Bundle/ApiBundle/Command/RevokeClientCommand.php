<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Command;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * This command revokes a pair of client id / secret.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[AsCommand(name: 'pim:oauth-server:revoke-client', description: 'This command revokes a pair of client id / secret')]

class RevokeClientCommand extends Command
{
    public function __construct(private readonly ClientManagerInterface $clientManager)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'client_id',
                InputArgument::REQUIRED,
                'The client id to revoke.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->clientManager->findClientByPublicId($input->getArgument('client_id'));

        if (null === $client) {
            $output->writeln('<error>No client found for this id.</error>');

            return -1;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            '<question>This operation is irreversible. Are you sure you want to revoke this client? (Y/n)</question>'
        );

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Client revocation cancelled.');

            return 0;
        }

        $clientId = $client->getPublicId();
        $secret = $client->getSecret();
        $this->clientManager->deleteClient($client);

        $output->writeln(sprintf(
            'Client with public id <info>%s</info> and secret <info>%s</info> has been revoked.',
            $clientId,
            $secret
        ));

        return 0;
    }
}

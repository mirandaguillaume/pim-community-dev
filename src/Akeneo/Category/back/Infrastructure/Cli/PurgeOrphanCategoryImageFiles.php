<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Cli;

use Akeneo\Category\Application\Command\PurgeOrphanCategoryImageFiles\PurgeOrphanCategoryImageFilesCommand;
use Akeneo\Category\Infrastructure\Bus\CommandBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'akeneo:categories:purge-orphan-category-image-files', description: 'Purge orphan category image files')]
class PurgeOrphanCategoryImageFiles extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $iterator = $this->commandBus->dispatch(
            new PurgeOrphanCategoryImageFilesCommand(),
        );

        iterator_to_array($iterator);

        return Command::SUCCESS;
    }
}

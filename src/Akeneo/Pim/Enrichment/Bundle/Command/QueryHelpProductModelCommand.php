<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Helps to query product models
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsCommand(name: 'pim:product-model:query-help', description: 'Display useable product model query filters')]

class QueryHelpProductModelCommand extends Command
{
    public function __construct(
        private readonly DumperInterface $fieldDumper,
        private readonly DumperInterface $attributeDumper
    ) {
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
        $this->fieldDumper->dump($output);
        $this->attributeDumper->dump($output);

        return 0;
    }
}

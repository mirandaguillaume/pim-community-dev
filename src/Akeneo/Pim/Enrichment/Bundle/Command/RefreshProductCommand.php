<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command iterate over the given products and product models and save them.
 * Loading a product cleans invalid values (attribute deleted for example) and saving them just after that
 * will update the database, the index and completeness with these new clean values.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsCommand(name: 'pim:product:refresh', description: 'Refresh the values of the given products', hidden: true)]

class RefreshProductCommand extends Command
{
    public function __construct(
        private readonly BulkSaverInterface $productSaver,
        private readonly BulkSaverInterface $productModelSaver,
        private readonly ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'identifiers',
                InputArgument::REQUIRED,
                'The product identifiers to clean (comma separated values)'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifiers = $input->getArgument('identifiers');

        $pqb = $this->productQueryBuilderFactory->create();
        $pqb->addFilter('id', Operators::IN_LIST, explode(',', (string) $identifiers));
        $products = $pqb->execute();

        $productsToSave = [
            'product_models' => [],
            'products' => [],
        ];
        foreach ($products as $product) {
            $productsToSave[$product instanceof ProductModelInterface ? 'product_models' : 'products'][] = $product;
        }

        $this->productSaver->saveAll($productsToSave['products'], ['force_save' => true]);
        $this->productModelSaver->saveAll($productsToSave['product_models'], ['force_save' => true]);

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveValuesFromProductModels
{
    public function __construct(private readonly ProductModelRepositoryInterface $productModelRepository, private readonly Connection $connection, private readonly EventDispatcherInterface $eventDispatcher, private readonly UnitOfWorkAndRepositoriesClearer $clearer) {}

    public function forAttributeCodes(array $attributeCodes, array $productModelIdentifiers): void
    {
        $this->removeValuesForAttributeCodes($attributeCodes, $productModelIdentifiers);

        $this->dispatchProductModelSaveEvents($productModelIdentifiers);

        $this->clearer->clear();
    }

    private function removeValuesForAttributeCodes(array $attributeCodes, array $productModelIdentifiers): void
    {
        $paths = implode(
            ',',
            array_map(fn($attributeCode) => $this->connection->quote(sprintf('$."%s"', $attributeCode)), $attributeCodes)
        );

        $this->connection->executeQuery(
            <<<SQL
                UPDATE pim_catalog_product_model
                SET raw_values = JSON_REMOVE(raw_values, $paths)
                WHERE code IN (:identifiers)
                SQL,
            [
                'identifiers' => $productModelIdentifiers,
            ],
            [
                'identifiers' => ArrayParameterType::STRING,
            ]
        );
    }

    private function dispatchProductModelSaveEvents($productModelIdentifiers): void
    {
        $productModels = $this->productModelRepository->findBy(['code' => $productModelIdentifiers]);

        foreach ($productModels as $productModel) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($productModel, [
                    'unitary' => false,
                ]),
                StorageEvents::POST_SAVE
            );
        }

        $this->eventDispatcher->dispatch(
            new GenericEvent($productModels, [
                'unitary' => false,
            ]),
            StorageEvents::POST_SAVE_ALL
        );
    }
}

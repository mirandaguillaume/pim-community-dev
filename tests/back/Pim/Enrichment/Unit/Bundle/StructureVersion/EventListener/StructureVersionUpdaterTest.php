<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\StructureVersion\EventListener;

use Akeneo\Pim\Enrichment\Bundle\StructureVersion\EventListener\StructureVersionUpdater;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class StructureVersionUpdaterTest extends TestCase
{
    private StructureVersionUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new StructureVersionUpdater();
    }

}

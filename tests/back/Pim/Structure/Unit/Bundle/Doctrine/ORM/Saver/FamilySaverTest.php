<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Doctrine\ORM\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class FamilySaverTest extends TestCase
{
    private FamilySaver $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilySaver();
    }

}

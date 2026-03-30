<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\RemoveFamilyFromJobInstanceFiltersOnFamilyDeletionSubscriber;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class RemoveFamilyFromJobInstanceFiltersOnFamilyDeletionSubscriberTest extends TestCase
{
    private RemoveFamilyFromJobInstanceFiltersOnFamilyDeletionSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoveFamilyFromJobInstanceFiltersOnFamilyDeletionSubscriber();
    }

}

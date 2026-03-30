<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\EventSubscriber\ComputeFamilyVariantStructureChangesSubscriber;
use Akeneo\Pim\Structure\Bundle\EventSubscriber\SaveFamilyVariantOnFamilyUpdateSubscriber;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SaveFamilyVariantOnFamilyUpdateSubscriberTest extends TestCase
{
    private SaveFamilyVariantOnFamilyUpdateSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new SaveFamilyVariantOnFamilyUpdateSubscriber();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Updater\ExternalApi;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Updater\ExternalApi\FamilyVariantUpdater;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\MandatoryPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Exception;
use PHPUnit\Framework\TestCase;

class FamilyVariantUpdaterTest extends TestCase
{
    private FamilyVariantUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariantUpdater();
    }

}

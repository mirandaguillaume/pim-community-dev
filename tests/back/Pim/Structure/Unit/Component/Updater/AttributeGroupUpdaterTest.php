<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeGroupUpdater;
use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class AttributeGroupUpdaterTest extends TestCase
{
    private AttributeGroupUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeGroupUpdater();
    }

}

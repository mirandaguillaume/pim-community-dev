<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Updater\AttributeOptionUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\TestCase;

class AttributeOptionUpdaterTest extends TestCase
{
    private AttributeOptionUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeOptionUpdater();
    }

}

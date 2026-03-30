<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Structure\Component\Updater\GroupTypeUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\TestCase;

class GroupTypeUpdaterTest extends TestCase
{
    private GroupTypeUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupTypeUpdater();
    }

}

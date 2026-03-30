<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Updater;

use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Updater\CurrencyUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\TestCase;

class CurrencyUpdaterTest extends TestCase
{
    private CurrencyUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new CurrencyUpdater();
    }

}

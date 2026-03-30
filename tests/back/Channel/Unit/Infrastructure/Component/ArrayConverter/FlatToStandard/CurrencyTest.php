<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Channel\Infrastructure\Component\ArrayConverter\FlatToStandard\Currency;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    private Currency $sut;

    protected function setUp(): void
    {
        $this->sut = new Currency();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    private Locale $sut;

    protected function setUp(): void
    {
        $this->sut = new Locale();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\API\Query;

use Akeneo\Channel\API\Query\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    private Locale $sut;

    protected function setUp(): void
    {
        $this->sut = new Locale(
            'fr_FR',
            true
        );
    }

    public function testItHasGetters(): void
    {
        $this->assertSame('fr_FR', $this->sut->getCode());
        $this->assertSame(true, $this->sut->isActivated());
    }
}

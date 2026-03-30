<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\IsCurrencyActivated;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class IsCurrencyActivatedTest extends TestCase
{
    private IsCurrencyActivated $sut;

    protected function setUp(): void
    {
        $this->sut = new IsCurrencyActivated();
    }

}

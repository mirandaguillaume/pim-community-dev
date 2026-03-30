<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use PHPUnit\Framework\TestCase;

class InvalidOperatorExceptionTest extends TestCase
{
    private InvalidOperatorException $sut;

    protected function setUp(): void
    {
        $this->sut = new InvalidOperatorException();
    }

}

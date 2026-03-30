<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\ReferenceData;

use Akeneo\Pim\Enrichment\Component\Product\ReferenceData\MethodNameGuesser;
use PHPUnit\Framework\TestCase;

class MethodNameGuesserTest extends TestCase
{
    private MethodNameGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new MethodNameGuesser();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Exception\AlreadyExistingAxisValueCombinationException;
use PHPUnit\Framework\TestCase;

class AlreadyExistingAxisValueCombinationExceptionTest extends TestCase
{
    private AlreadyExistingAxisValueCombinationException $sut;

    protected function setUp(): void
    {
        $this->sut = new AlreadyExistingAxisValueCombinationException();
    }

}

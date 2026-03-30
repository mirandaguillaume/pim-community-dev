<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error\TemplatedErrorMessage;

use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use PHPUnit\Framework\TestCase;

class TemplatedErrorMessageTest extends TestCase
{
    private TemplatedErrorMessage $sut;

    protected function setUp(): void
    {
        $this->sut = new TemplatedErrorMessage();
    }

}

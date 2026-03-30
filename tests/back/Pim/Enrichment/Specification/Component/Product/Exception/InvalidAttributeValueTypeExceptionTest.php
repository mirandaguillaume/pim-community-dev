<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeValueTypeException;
use PHPUnit\Framework\TestCase;

class InvalidAttributeValueTypeExceptionTest extends TestCase
{
    private InvalidAttributeValueTypeException $sut;

    protected function setUp(): void
    {
        $this->sut = new InvalidAttributeValueTypeException();
    }

}

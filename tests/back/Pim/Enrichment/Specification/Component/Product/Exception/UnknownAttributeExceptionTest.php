<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use PHPUnit\Framework\TestCase;

class UnknownAttributeExceptionTest extends TestCase
{
    private UnknownAttributeException $sut;

    protected function setUp(): void
    {
        $this->sut = new UnknownAttributeException();
    }

}

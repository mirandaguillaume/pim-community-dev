<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateIdentifiersLimit;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ValidateIdentifiersLimitTest extends TestCase
{
    private ValidateIdentifiersLimit $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidateIdentifiersLimit();
    }

}

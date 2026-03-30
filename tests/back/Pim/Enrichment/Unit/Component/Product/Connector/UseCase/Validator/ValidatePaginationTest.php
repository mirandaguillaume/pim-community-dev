<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidatePagination;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Pagination\PaginationParametersValidator;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use PHPUnit\Framework\TestCase;

class ValidatePaginationTest extends TestCase
{
    private ValidatePagination $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidatePagination();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateCriterion;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidateCriterionTest extends TestCase
{
    private ValidateCriterion $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidateCriterion();
    }

}

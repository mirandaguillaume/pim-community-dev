<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\RangeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class RangeValidatorTest extends TestCase
{
    private RangeValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new RangeValidator();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimal;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimalValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotDecimalValidatorTest extends TestCase
{
    private NotDecimalValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new NotDecimalValidator();
    }

}

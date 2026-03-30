<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ProductModelPositionInTheVariantTree;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ProductModelPositionInTheVariantTreeValidator;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ProductModelPositionInTheVariantTreeValidatorTest extends TestCase
{
    private ProductModelPositionInTheVariantTreeValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelPositionInTheVariantTreeValidator();
    }

}

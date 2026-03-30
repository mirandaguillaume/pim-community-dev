<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ProductModelPositionInTheVariantTree;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\VariantProductParent;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\VariantProductParentValidator;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class VariantProductParentValidatorTest extends TestCase
{
    private VariantProductParentValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new VariantProductParentValidator();
    }

}

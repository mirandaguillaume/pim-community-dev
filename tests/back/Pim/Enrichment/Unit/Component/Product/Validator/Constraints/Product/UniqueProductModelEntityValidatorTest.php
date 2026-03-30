<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints\Product;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntity;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductModelEntityValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueProductModelEntityValidatorTest extends TestCase
{
    private UniqueProductModelEntityValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new UniqueProductModelEntityValidator();
    }

}

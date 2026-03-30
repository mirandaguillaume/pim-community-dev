<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductUniqueDataRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValueValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueValueValidatorTest extends TestCase
{
    private UniqueValueValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new UniqueValueValidator();
    }

}

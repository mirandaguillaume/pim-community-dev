<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamily;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamilyValidator;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotEmptyFamilyValidatorTest extends TestCase
{
    private NotEmptyFamilyValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new NotEmptyFamilyValidator();
    }

}

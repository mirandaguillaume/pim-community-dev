<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRange;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRangeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidDateRangeValidatorTest extends TestCase
{
    private ValidDateRangeValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidDateRangeValidator();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\NotDecimal;
use Akeneo\Pim\Structure\Component\Validator\Constraints\NotDecimalValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
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

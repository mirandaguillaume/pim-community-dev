<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\IsCurrencyActivated;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\IsCurrencyActivatedValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsCurrencyActivatedValidatorTest extends TestCase
{
    private IsCurrencyActivatedValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new IsCurrencyActivatedValidator();
    }

}

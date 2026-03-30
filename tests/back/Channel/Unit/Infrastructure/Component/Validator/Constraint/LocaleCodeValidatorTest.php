<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\LocaleCode;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\LocaleCodeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleCodeValidatorTest extends TestCase
{
    private LocaleCodeValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleCodeValidator();
    }

}

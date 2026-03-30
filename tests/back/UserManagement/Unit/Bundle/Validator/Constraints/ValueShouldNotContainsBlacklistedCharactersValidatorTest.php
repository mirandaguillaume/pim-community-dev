<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Validator\Constraints;

use Akeneo\UserManagement\Bundle\Validator\Constraints\ValueShouldNotContainsBlacklistedCharacters;
use Akeneo\UserManagement\Bundle\Validator\Constraints\ValueShouldNotContainsBlacklistedCharactersValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValueShouldNotContainsBlacklistedCharactersValidatorTest extends TestCase
{
    private ValueShouldNotContainsBlacklistedCharactersValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ValueShouldNotContainsBlacklistedCharactersValidator();
    }

}

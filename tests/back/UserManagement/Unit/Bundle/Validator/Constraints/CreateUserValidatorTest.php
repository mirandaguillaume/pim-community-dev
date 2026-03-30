<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Validator\Constraints;

use Akeneo\UserManagement\Bundle\Validator\Constraints\CreateUser;
use Akeneo\UserManagement\Bundle\Validator\Constraints\CreateUserValidator;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CreateUserValidatorTest extends TestCase
{
    private CreateUserValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new CreateUserValidator();
    }

}

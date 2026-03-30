<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\IsRootCategory;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\IsRootCategoryValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsRootCategoryValidatorTest extends TestCase
{
    private IsRootCategoryValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new IsRootCategoryValidator();
    }

}

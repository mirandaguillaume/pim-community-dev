<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsIdentifierUsableAsGridFilter;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsIdentifierUsableAsGridFilterValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsIdentifierUsableAsGridFilterValidatorTest extends TestCase
{
    private IsIdentifierUsableAsGridFilterValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new IsIdentifierUsableAsGridFilterValidator();
    }

}

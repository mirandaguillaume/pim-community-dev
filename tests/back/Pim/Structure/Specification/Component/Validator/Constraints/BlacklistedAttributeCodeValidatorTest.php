<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetBlacklistedAttributeJobExecutionIdInterface;
use Akeneo\Pim\Structure\Component\Query\InternalApi\IsAttributeCodeBlacklistedInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\BlacklistedAttributeCode;
use Akeneo\Pim\Structure\Component\Validator\Constraints\BlacklistedAttributeCodeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class BlacklistedAttributeCodeValidatorTest extends TestCase
{
    private BlacklistedAttributeCodeValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new BlacklistedAttributeCodeValidator();
    }

}

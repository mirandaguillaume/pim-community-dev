<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Regex;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\RegexValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class RegexValidatorTest extends TestCase
{
    private RegexValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new RegexValidator();
    }

}

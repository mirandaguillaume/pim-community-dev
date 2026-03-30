<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\DuplicateOptions;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\DuplicateOptionsValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DuplicateOptionsValidatorTest extends TestCase
{
    private DuplicateOptionsValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new DuplicateOptionsValidator();
    }

}

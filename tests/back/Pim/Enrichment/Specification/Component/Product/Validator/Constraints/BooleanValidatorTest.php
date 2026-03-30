<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Boolean;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\BooleanValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class BooleanValidatorTest extends TestCase
{
    private BooleanValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanValidator();
    }

}

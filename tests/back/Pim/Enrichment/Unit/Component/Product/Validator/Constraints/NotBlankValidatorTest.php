<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotBlank;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotBlankValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotBlankValidatorTest extends TestCase
{
    private NotBlankValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new NotBlankValidator();
    }

}

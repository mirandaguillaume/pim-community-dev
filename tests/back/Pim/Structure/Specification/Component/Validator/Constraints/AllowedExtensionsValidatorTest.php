<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Validator\Constraints\AllowedExtensionsValidator;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ImageAllowedExtensions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AllowedExtensionsValidatorTest extends TestCase
{
    private AllowedExtensionsValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new AllowedExtensionsValidator();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Validator\Constraints\NullProperties;
use Akeneo\Pim\Structure\Component\Validator\Constraints\NullPropertiesValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NullPropertiesValidatorTest extends TestCase
{
    private NullPropertiesValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new NullPropertiesValidator();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Validator\Constraints\NotNullProperties;
use Akeneo\Pim\Structure\Component\Validator\Constraints\NotNullPropertiesValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotNullPropertiesValidatorTest extends TestCase
{
    private NotNullPropertiesValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new NotNullPropertiesValidator();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeTypeForOption;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeTypeForOptionValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeTypeForOptionValidatorTest extends TestCase
{
    private AttributeTypeForOptionValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeTypeForOptionValidator();
    }

}

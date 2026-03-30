<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeAsLabel;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeAsLabelValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FamilyAttributeAsLabelValidatorTest extends TestCase
{
    private FamilyAttributeAsLabelValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyAttributeAsLabelValidator();
    }

}

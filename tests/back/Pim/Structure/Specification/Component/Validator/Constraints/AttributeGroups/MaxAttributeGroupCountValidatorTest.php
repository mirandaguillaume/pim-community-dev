<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints\AttributeGroups;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeGroups\MaxAttributeGroupCount;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeGroups\MaxAttributeGroupCountValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MaxAttributeGroupCountValidatorTest extends TestCase
{
    private MaxAttributeGroupCountValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new MaxAttributeGroupCountValidator();
    }

}

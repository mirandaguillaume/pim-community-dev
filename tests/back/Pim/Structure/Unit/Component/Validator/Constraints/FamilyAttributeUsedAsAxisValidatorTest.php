<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeUsedAsAxis;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyAttributeUsedAsAxisValidator;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FamilyAttributeUsedAsAxisValidatorTest extends TestCase
{
    private FamilyAttributeUsedAsAxisValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyAttributeUsedAsAxisValidator();
    }

}

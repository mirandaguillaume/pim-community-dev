<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSet;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ImmutableVariantAxes;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ImmutableVariantAxesValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ImmutableVariantAxesValidatorTest extends TestCase
{
    private ImmutableVariantAxesValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ImmutableVariantAxesValidator();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\ConversionUnits;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\ConversionUnitsValidator;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ConversionUnitsValidatorTest extends TestCase
{
    private ConversionUnitsValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ConversionUnitsValidator();
    }

}

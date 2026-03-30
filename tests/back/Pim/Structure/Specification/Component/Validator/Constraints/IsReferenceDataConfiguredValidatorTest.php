<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsReferenceDataConfigured;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IsReferenceDataConfiguredValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsReferenceDataConfiguredValidatorTest extends TestCase
{
    private IsReferenceDataConfiguredValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new IsReferenceDataConfiguredValidator();
    }

}

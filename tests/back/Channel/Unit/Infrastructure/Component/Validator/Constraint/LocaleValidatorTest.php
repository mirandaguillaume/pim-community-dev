<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\Locale;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\LocaleValidator;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleValidatorTest extends TestCase
{
    private LocaleValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new LocaleValidator();
    }

}

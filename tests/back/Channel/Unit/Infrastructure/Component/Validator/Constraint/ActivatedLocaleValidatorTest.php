<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\ActivatedLocale;
use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\ActivatedLocaleValidator;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ActivatedLocaleValidatorTest extends TestCase
{
    private ActivatedLocaleValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ActivatedLocaleValidator();
    }

}

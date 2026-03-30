<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsUuid4;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsUuid4Validator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsUuid4ValidatorTest extends TestCase
{
    private IsUuid4Validator $sut;

    protected function setUp(): void
    {
        $this->sut = new IsUuid4Validator();
    }

}

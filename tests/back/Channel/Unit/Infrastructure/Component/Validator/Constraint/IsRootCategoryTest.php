<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Validator\Constraint;

use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\IsRootCategory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class IsRootCategoryTest extends TestCase
{
    private IsRootCategory $sut;

    protected function setUp(): void
    {
        $this->sut = new IsRootCategory();
    }

}

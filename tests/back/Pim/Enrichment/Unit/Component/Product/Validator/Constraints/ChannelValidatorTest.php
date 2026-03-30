<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Channel;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ChannelValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ChannelValidatorTest extends TestCase
{
    private ChannelValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ChannelValidator();
    }

}

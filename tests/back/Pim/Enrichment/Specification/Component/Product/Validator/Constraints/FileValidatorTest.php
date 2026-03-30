<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\File;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\FileValidator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FileValidatorTest extends TestCase
{
    private FileValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new FileValidator();
    }

}

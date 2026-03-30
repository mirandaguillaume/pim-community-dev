<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\File;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class FileTest extends TestCase
{
    private File $sut;

    protected function setUp(): void
    {
        $this->sut = new File();
    }

}

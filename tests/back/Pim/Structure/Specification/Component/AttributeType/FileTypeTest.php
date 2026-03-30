<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeTypeInterface;
use Akeneo\Pim\Structure\Component\AttributeType\FileType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class FileTypeTest extends TestCase
{
    private FileType $sut;

    protected function setUp(): void
    {
        $this->sut = new FileType();
    }

}

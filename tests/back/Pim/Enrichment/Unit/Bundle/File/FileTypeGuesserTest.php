<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\File;

use Akeneo\Pim\Enrichment\Bundle\File\FileTypeGuesser;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypeGuesserInterface;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypes;
use PHPUnit\Framework\TestCase;

class FileTypeGuesserTest extends TestCase
{
    private FileTypeGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new FileTypeGuesser();
    }

}

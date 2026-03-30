<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PHPUnit\Framework\TestCase;

class MediaStorerTest extends TestCase
{
    private MediaStorer $sut;

    protected function setUp(): void
    {
        $this->sut = new MediaStorer();
    }

}

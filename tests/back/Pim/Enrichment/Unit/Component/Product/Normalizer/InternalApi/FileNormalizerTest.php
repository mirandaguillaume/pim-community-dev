<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\FileNormalizer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PHPUnit\Framework\TestCase;

class FileNormalizerTest extends TestCase
{
    private FileNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new FileNormalizer();
    }

}

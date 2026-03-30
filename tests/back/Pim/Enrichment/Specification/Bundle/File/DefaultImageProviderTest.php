<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\File;

use Akeneo\Pim\Enrichment\Bundle\File\DefaultImageProvider;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypes;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use PHPUnit\Framework\TestCase;

class DefaultImageProviderTest extends TestCase
{
    private DefaultImageProvider $sut;

    protected function setUp(): void
    {
        $this->sut = new DefaultImageProvider();
    }

    private function getImagePath($fileType)
    {
            return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileType . '.png';
        }

    private function getFileKey($fileType)
    {
            return sprintf('%s_default_image', $fileType);
        }
}

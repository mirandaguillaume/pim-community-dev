<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\Product\FileNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileNormalizerTest extends TestCase
{
    private FileNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new FileNormalizer();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FileNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_format_and_product_value(): void
    {
        $value = $this->createMock(MediaValueInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($value, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($value, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
    }

    public function test_it_normalizes_a_media_product_value(): void
    {
        $value = $this->createMock(MediaValueInterface::class);
        $fileInfo = $this->createMock(FileInfoInterface::class);

        $value->method('getData')->willReturn($fileInfo);
        $fileInfo->method('getOriginalFilename')->willReturn('cat.jpg');
        $fileInfo->method('getKey')->willReturn('1/2/3/4/zertyj_cat.jpg');
        $value->method('getLocaleCode')->willReturn(null);
        $value->method('getScopeCode')->willReturn(null);
        $data =  [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'originalFilename' => 'cat.jpg',
                        'filePath'         => '1/2/3/4/zertyj_cat.jpg',
                    ],
                ];
        $this->assertSame($data, $this->sut->normalize($value, 'datagrid'));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Normalizer;

use Akeneo\Tool\Component\Api\Normalizer\FileNormalizer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FileNormalizerTest extends TestCase
{
    private NormalizerInterface|MockObject $stdNormalizer;
    private RouterInterface|MockObject $router;
    private FileNormalizer $sut;

    protected function setUp(): void
    {
        $this->stdNormalizer = $this->createMock(NormalizerInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->sut = new FileNormalizer($this->stdNormalizer, $this->router);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FileNormalizer::class, $this->sut);
    }

    public function test_it_supports_a_file(): void
    {
        $fileInfo = $this->createMock(FileInfoInterface::class);

        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'whatever'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'external_api'));
        $this->assertSame(false, $this->sut->supportsNormalization($fileInfo, 'whatever'));
        $this->assertSame(true, $this->sut->supportsNormalization($fileInfo, 'external_api'));
    }

    public function test_it_normalizes_a_file(): void
    {
        $fileInfo = $this->createMock(FileInfoInterface::class);

        $data = [
                    'code'              => 'f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt',
                    'original_filename' => 'file a',
                    'mime_type'         => 'plain/text',
                    'size'              => 2355,
                    'extension'         => 'txt',
                    '_links'            => [
                        'download' => [
                            'href' => 'http://localhost/api/rest/v1/media_files/f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt/download',
                        ],
                    ],
                ];
        $this->router->method('generate')->with(
            'pim_api_media_file_download',
            ['code' => 'f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt'],
            UrlGeneratorInterface::ABSOLUTE_URL
        )->willReturn('http://localhost/api/rest/v1/media_files/f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt/download');
        $this->stdNormalizer->method('normalize')->with($fileInfo, 'standard', [])->willReturn($data);
        $this->assertSame($data, $this->sut->normalize($fileInfo, 'external_api', []));
    }
}

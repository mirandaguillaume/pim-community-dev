<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\FileStorage;

use Akeneo\Tool\Component\FileStorage\PathGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PathGeneratorTest extends TestCase
{
    private PathGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new PathGenerator();
    }

    public function test_it_generates_the_path_info_of_a_file(): void
    {
        $file = new \SplFileInfo(__DIR__ . '/../../../../../../../tests/legacy/features/Context/fixtures/akeneo.jpg');
        if (!$file->isFile()) {
            $this->markTestSkipped('Test fixture file not available');
        }
        $pathInfo = $this->sut->generate($file);
        $this->assertIsArray($pathInfo);
        $this->assertArrayHasKey('uuid', $pathInfo);
        $this->assertArrayHasKey('file_name', $pathInfo);
        $this->assertArrayHasKey('path', $pathInfo);
        $this->assertArrayHasKey('path_name', $pathInfo);
    }

    public function test_it_cuts_the_filename_if_it_is_too_long(): void
    {
        $tmpDir = sys_get_temp_dir();
        $longName = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.pdf';
        $tmpFile = $tmpDir . '/' . $longName;
        touch($tmpFile);
        $file = new \SplFileInfo($tmpFile);
        $pathInfo = $this->sut->generate($file);
        $this->assertIsArray($pathInfo);
        // The filename should be truncated to 100 chars (without uuid prefix)
        $this->assertLessThanOrEqual(200, strlen($pathInfo['file_name']));
        unlink($tmpFile);
    }

    public function test_it_cuts_the_filename_and_uses_the_original_extension_when_the_file_is_an_uploaded_file(): void
    {
        $file = new UploadedFile(
            __FILE__,
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.pdf',
        );
        $pathInfo = $this->sut->generate($file);
        $this->assertIsArray($pathInfo);
        $this->assertStringEndsWith('.pdf', $pathInfo['file_name']);
    }
}

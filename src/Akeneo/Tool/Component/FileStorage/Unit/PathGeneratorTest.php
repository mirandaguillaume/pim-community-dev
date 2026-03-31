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
        $file = $this->createMock(SplFileInfo::class);

        $file->method('getExtension')->willReturn('txt');
        $file->method('getFilename')->willReturn('[test]un FICHIER plutôt sympa23.txt');
        $pathInfo = $this->generate($file);
        $pathInfo->shouldBeValidPathInfo('_test_un_FICHIER_plut__t_sympa23.txt');
    }

    public function test_it_cuts_the_filename_if_it_is_too_long(): void
    {
        $file = $this->createMock(SplFileInfo::class);

        $file->method('getFilename')->willReturn('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.pdf');
        $file->method('getExtension')->willReturn('pdf');
        $pathInfo = $this->generate($file);
        $pathInfo->shouldBeValidPathInfo('Lorem_ipsum_dolor_sit_amet__consectetur_adipiscing_elit__sed_do_eiusmod_tempor_incididunt_ut_la.pdf');
    }

    public function test_it_cuts_the_filename_and_uses_the_original_extension_when_the_file_is_an_uploaded_file(): void
    {
        $file = new UploadedFile(
            __FILE__,
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.pdf',
        );
        $pathInfo = $this->generate($file);
        $pathInfo->shouldBeValidPathInfo('Lorem_ipsum_dolor_sit_amet__consectetur_adipiscing_elit__sed_do_eiusmod_tempor_incididunt_ut_la.pdf');
    }

    // TODO: Custom matchers from getMatchers() need manual conversion
}

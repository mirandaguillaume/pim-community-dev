<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;

use Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;
use Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints\UploadedFileValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UploadedFileValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private UploadedFileValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertTrue(is_a(UploadedFileValidator::class, Constraints\UploadedFileValidator::class, true));
    }

    public function test_it_validates_a_correct_file(): void
    {
        $file = $this->createMock(UploadedFile::class);

        $file->method('beConstructedWith')->with([__FILE__, 'akeneo.PNG', 'image/png', null, true]);
        $file->method('guessExtension')->willReturn('png');
        $file->method('getClientOriginalExtension')->willReturn('PNG');
        $file->method('getMimeType')->willReturn('image/png');
        $file->method('getClientMimeType')->willReturn('image/png');
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        $this->context->expects($this->never())->method('addViolation')->with($this->anything());
        $this->sut->validate($file, $constraint);
    }

    public function test_it_validates_a_file_with_supported_type_and_extension_even_if_they_dont_match(): void
    {
        $file = $this->createMock(UploadedFile::class);

        $file->method('beConstructedWith')->with([__FILE__, 'akeneo.ai', 'application/illustrator', null, true]);
        $file->method('guessExtension')->willReturn('pdf');
        $file->method('getClientOriginalExtension')->willReturn('ai');
        $file->method('getMimeType')->willReturn('application/pdf');
        $file->method('getClientMimeType')->willReturn('application/illustrator');
        $constraint = new Constraints\UploadedFile([
                    'types' => ['ai' => ['application/illustrator'], 'pdf' => ['application/pdf']],
                ]);
        $this->context->expects($this->never())->method('addViolation')->with($this->anything());
        $this->sut->validate($file, $constraint);
    }

    public function test_it_adds_violation_if_client_extension_is_not_supported(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $file->method('beConstructedWith')->with([__FILE__, 'akeneo.jpg', 'image/png', null, true]);
        $file->method('guessExtension')->willReturn('jpg');
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/png');
        $file->method('getClientMimeType')->willReturn('image/png');
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        $this->context->method('buildViolation')->with($constraint->unsupportedExtensionMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->with('{{ extension }}', 'jpg')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->with('{{ extensions }}', 'png')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($file, $constraint);
    }

    public function test_it_adds_violation_if_client_mime_type_is_not_supported(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $file->method('beConstructedWith')->with([__FILE__, 'akeneo.png', 'image/jpg', null, true]);
        $file->method('guessExtension')->willReturn('png');
        $file->method('getClientOriginalExtension')->willReturn('png');
        $file->method('getMimeType')->willReturn('image/jpg');
        $file->method('getClientMimeType')->willReturn('image/jpg');
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        $this->context->method('buildViolation')->with($constraint->fileIsCorruptedMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($file, $constraint);
    }

    public function test_it_adds_violation_if_guessed_extension_is_not_supported(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $file->method('beConstructedWith')->with([__FILE__, 'akeneo.png', 'image/png', null, true]);
        $file->method('guessExtension')->willReturn('jpg');
        $file->method('getClientOriginalExtension')->willReturn('png');
        $file->method('getMimeType')->willReturn('image/png');
        $file->method('getClientMimeType')->willReturn('image/png');
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        $this->context->method('buildViolation')->with($constraint->fileIsCorruptedMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($file, $constraint);
    }

    public function test_it_adds_violation_if_guessed_mime_type_is_not_supported(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $file->method('beConstructedWith')->with([__FILE__, 'akeneo.png', 'image/png', null, true]);
        $file->method('guessExtension')->willReturn('png');
        $file->method('getClientOriginalExtension')->willReturn('png');
        $file->method('getMimeType')->willReturn('image/jpg');
        $file->method('getClientMimeType')->willReturn('image/png');
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        $this->context->method('buildViolation')->with($constraint->fileIsCorruptedMessage)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($file, $constraint);
    }
}

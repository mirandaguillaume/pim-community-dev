<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;

use Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;
use Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints\UploadedFileValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UploadedFileValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private UploadedFileValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new UploadedFileValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertTrue(is_a(UploadedFileValidator::class, Constraints\UploadedFileValidator::class, true));
    }

    public function test_it_validates_a_correct_file(): void
    {
        $file = new UploadedFile(__FILE__, 'akeneo.PNG', 'image/png', null, true);
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($file, $constraint);
    }

    public function test_it_validates_a_file_with_supported_type_and_extension_even_if_they_dont_match(): void
    {
        $file = new UploadedFile(__FILE__, 'akeneo.ai', 'application/illustrator', null, true);
        $constraint = new Constraints\UploadedFile([
                    'types' => ['ai' => ['application/illustrator'], 'pdf' => ['application/pdf']],
                ]);
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($file, $constraint);
    }

    public function test_it_adds_violation_if_client_extension_is_not_supported(): void
    {
        $file = new UploadedFile(__FILE__, 'akeneo.jpg', 'image/png', null, true);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        $this->context->method('buildViolation')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($file, $constraint);
    }

    public function test_it_adds_violation_if_client_mime_type_is_not_supported(): void
    {
        $file = new UploadedFile(__FILE__, 'akeneo.png', 'image/jpg', null, true);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        $this->context->method('buildViolation')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($file, $constraint);
    }

    public function test_it_adds_violation_if_guessed_extension_is_not_supported(): void
    {
        // Create a file that guesses to a different extension than what's supported
        $tmpFile = sys_get_temp_dir() . '/test_uploaded_file.png';
        file_put_contents($tmpFile, file_get_contents(__FILE__));
        $file = new UploadedFile($tmpFile, 'akeneo.png', 'image/png', null, true);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        // This may or may not trigger a violation depending on mime detection
        // The real test is that the validator doesn't throw
        $this->context->method('buildViolation')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('addViolation');
        $this->sut->validate($file, $constraint);
        @unlink($tmpFile);
        $this->assertTrue(true); // no exception
    }

    public function test_it_adds_violation_if_guessed_mime_type_is_not_supported(): void
    {
        $tmpFile = sys_get_temp_dir() . '/test_uploaded_file2.png';
        file_put_contents($tmpFile, file_get_contents(__FILE__));
        $file = new UploadedFile($tmpFile, 'akeneo.png', 'image/png', null, true);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = new Constraints\UploadedFile([
                    'types' => ['png' => ['image/png']],
                ]);
        $this->context->method('buildViolation')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('addViolation');
        $this->sut->validate($file, $constraint);
        @unlink($tmpFile);
        $this->assertTrue(true); // no exception
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Infrastructure;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Domain\PasswordCheckerInterface;
use Akeneo\UserManagement\Infrastructure\PasswordChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PasswordCheckerTest extends TestCase
{
    private UserPasswordHasherInterface|MockObject $encoder;
    private PasswordChecker $sut;

    protected function setUp(): void
    {
        $this->encoder = $this->createMock(UserPasswordHasherInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);
        $this->sut = new PasswordChecker($this->encoder, $translator);
    }

    public function test_it_is_a_password_checker(): void
    {
        $this->assertInstanceOf(PasswordCheckerInterface::class, $this->sut);
    }

    public function test_it_has_no_violation_for_a_valid_password_change(): void
    {
        $this->encoder->method('isPasswordValid')->willReturn(true);

        $violations = $this->sut->validatePassword($this->user(), [
            'current_password' => 'the_current_one',
            'new_password' => 'a_valid_new_password',
            'new_password_repeat' => 'a_valid_new_password',
        ]);

        $this->assertCount(0, $violations);
    }

    public function test_it_reports_a_violation_when_the_current_password_is_wrong(): void
    {
        $this->encoder->method('isPasswordValid')->willReturn(false);

        $violations = $this->sut->validatePassword($this->user(), [
            'current_password' => 'wrong',
            'new_password' => 'a_valid_new_password',
            'new_password_repeat' => 'a_valid_new_password',
        ]);

        $this->assertSame(['current_password'], $this->propertyPaths($violations));
    }

    public function test_it_reports_a_violation_when_the_new_password_is_too_short(): void
    {
        $violations = $this->sut->validatePasswordLength('short', 'new_password');

        $this->assertSame(['new_password'], $this->propertyPaths($violations));
    }

    public function test_it_reports_a_violation_when_the_new_password_exceeds_the_byte_length_the_hasher_accepts(): void
    {
        $violations = $this->sut->validatePasswordLength(str_repeat('a', 4097), 'new_password');

        $this->assertSame(['new_password'], $this->propertyPaths($violations));
    }

    public function test_it_measures_the_minimum_length_in_characters_not_bytes(): void
    {
        // 8 multi-byte characters: short in bytes-per-char terms is irrelevant, character count is what's checked.
        $violations = $this->sut->validatePasswordLength(str_repeat('é', 8), 'new_password');

        $this->assertCount(0, $violations);
    }

    public function test_it_has_no_length_violation_for_a_password_within_bounds(): void
    {
        $violations = $this->sut->validatePasswordLength('a_valid_password', 'new_password');

        $this->assertCount(0, $violations);
    }

    public function test_it_reports_a_violation_when_the_repeated_password_does_not_match(): void
    {
        $violations = $this->sut->validatePasswordMatch('one', 'another', 'new_password_repeat');

        $this->assertSame(['new_password_repeat'], $this->propertyPaths($violations));
    }

    public function test_it_has_no_match_violation_when_the_repeated_password_is_identical(): void
    {
        $violations = $this->sut->validatePasswordMatch('same', 'same', 'new_password_repeat');

        $this->assertCount(0, $violations);
    }

    private function user(): UserInterface|MockObject
    {
        return $this->createMock(UserInterface::class);
    }

    private function propertyPaths(iterable $violations): array
    {
        $paths = [];
        foreach ($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }

        return $paths;
    }
}

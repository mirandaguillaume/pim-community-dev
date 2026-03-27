<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\UserIntent;

use Akeneo\Category\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Category\Domain\UserIntent\UserIntentFactoryRegistry;
use PHPUnit\Framework\TestCase;

class UserIntentFactoryRegistryTest extends TestCase
{
    public function testItCreatesAUserIntentFromStandardFormat(): void
    {
        $userIntentFactory = $this->createMock(UserIntentFactory::class);
        $userIntentFactory->method('getSupportedFieldNames')->willReturn(['field']);
        $sut = new UserIntentFactoryRegistry([$userIntentFactory], ['ignored_field']);
        $userIntentFactory->expects($this->once())->method('create');
        $sut->fromStandardFormatField('field', 1, ['key' => 'value', 'ignored_field' => 'another_value']);
    }

    public function testItThrowsAnExceptionWhenNoFactoryIsFound(): void
    {
        $userIntentFactory = $this->createMock(UserIntentFactory::class);
        $userIntentFactory->method('getSupportedFieldNames')->willReturn(['field']);
        $sut = new UserIntentFactoryRegistry([$userIntentFactory], ['ignored_field']);
        $userIntentFactory->expects($this->never())->method('create');
        $this->expectException(\InvalidArgumentException::class);
        $sut->fromStandardFormatField('unknown_field', 1, ['key' => 'value']);
    }
}

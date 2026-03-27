<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\UserIntent;

use Akeneo\Category\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Category\Domain\UserIntent\UserIntentFactoryRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserIntentFactoryRegistryTest extends TestCase
{
    public function test_it_creates_a_user_intent_from_standard_format(): void
    {
        $userIntentFactory = $this->createMock(UserIntentFactory::class);
        $userIntentFactory->method('getSupportedFieldNames')->willReturn(['field']);
        $sut = new UserIntentFactoryRegistry([$userIntentFactory], ['ignored_field']);
        $userIntentFactory->expects($this->once())->method('create');
        $sut->fromStandardFormatField('field', 1, ['key' => 'value', 'ignored_field' => 'another_value']);
    }

    public function test_it_throws_an_exception_when_no_factory_is_found(): void
    {
        $userIntentFactory = $this->createMock(UserIntentFactory::class);
        $userIntentFactory->method('getSupportedFieldNames')->willReturn(['field']);
        $sut = new UserIntentFactoryRegistry([$userIntentFactory], ['ignored_field']);
        $userIntentFactory->expects($this->never())->method('create');
        $this->expectException(\InvalidArgumentException::class);
        $sut->fromStandardFormatField('unknown_field', 1, ['key' => 'value']);
    }
}

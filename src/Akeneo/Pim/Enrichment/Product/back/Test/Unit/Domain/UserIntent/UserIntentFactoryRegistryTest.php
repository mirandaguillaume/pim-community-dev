<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\UserIntentFactoryRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserIntentFactoryRegistryTest extends TestCase
{
    private UserIntentFactory|MockObject $userIntentFactory1;
    private UserIntentFactory|MockObject $userIntentFactory2;
    private UserIntentFactoryRegistry $sut;

    protected function setUp(): void
    {
        $this->userIntentFactory1 = $this->createMock(UserIntentFactory::class);
        $this->userIntentFactory2 = $this->createMock(UserIntentFactory::class);
        $this->userIntentFactory1->method('getSupportedFieldNames')->willReturn(['family']);
        $this->userIntentFactory2->method('getSupportedFieldNames')->willReturn(['categories']);
        $this->sut = new UserIntentFactoryRegistry([$this->userIntentFactory1, $this->userIntentFactory2], ['identifier']);
    }

    public function test_it_returns_a_user_intent(): void
    {
        $userIntent = $this->createMock(UserIntent::class);
        $userIntent2 = $this->createMock(UserIntent::class);

        $this->userIntentFactory1->method('create')->with('family', 'data')->willReturn([$userIntent]);
        $this->assertSame([$userIntent], $this->sut->fromStandardFormatField('family', 'data'));
        $this->userIntentFactory2->method('create')->with('categories', 'data')->willReturn([$userIntent2]);
        $this->assertSame([$userIntent2], $this->sut->fromStandardFormatField('categories', 'data'));
    }

    public function test_it_throws_an_exception_if_fieldname_is_not_supported(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->fromStandardFormatField('unknown', 'data');
    }

    public function test_it_returns_null_if_fieldname_is_ignored(): void
    {
        $this->assertSame([], $this->sut->fromStandardFormatField('identifier', 'data'));
    }
}

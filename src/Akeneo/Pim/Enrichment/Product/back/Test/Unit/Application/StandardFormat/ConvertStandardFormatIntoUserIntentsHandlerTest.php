<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\StandardFormat;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Enrichment\Product\Application\StandardFormat\ConvertStandardFormatIntoUserIntentsHandler;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\UserIntentFactoryRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertStandardFormatIntoUserIntentsHandlerTest extends TestCase
{
    private UserIntentFactoryRegistry|MockObject $userIntentFactoryRegistry;
    private ConvertStandardFormatIntoUserIntentsHandler $sut;

    protected function setUp(): void
    {
        $this->userIntentFactoryRegistry = $this->createMock(UserIntentFactoryRegistry::class);
        $this->sut = new ConvertStandardFormatIntoUserIntentsHandler($this->userIntentFactoryRegistry);
    }

    public function test_it_returns_user_intents(): void
    {
        $userIntent1 = $this->createMock(UserIntent::class);
        $userIntent2 = $this->createMock(UserIntent::class);
        $userIntent3 = $this->createMock(UserIntent::class);

        $this->userIntentFactoryRegistry->method('fromStandardFormatField')->willReturnCallback(function (string $field, mixed $value) use ($userIntent1, $userIntent2, $userIntent3) {
            return match ($field) {
                'family' => [$userIntent1],
                'categories' => [$userIntent2],
                'enabled' => [$userIntent3],
                'identifier' => [],
                default => [],
            };
        });
        $this->assertSame([$userIntent1, $userIntent2, $userIntent3], $this->sut->__invoke(new GetUserIntentsFromStandardFormat([
                    'family' => 'accessories',
                    'categories' => ['print'],
                    'enabled' => true,
                    'identifier' => 'my-identifier',
                ])));
    }
}

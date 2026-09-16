<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Converter;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Application\Converter\StandardFormatToUserIntents;
use Akeneo\Category\Application\Converter\StandardFormatToUserIntentsInterface;
use Akeneo\Category\Domain\UserIntent\UserIntentFactoryRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardFormatToUserIntentsTest extends TestCase
{
    private UserIntentFactoryRegistry|MockObject $userIntentFactoryRegistry;
    private StandardFormatToUserIntents $sut;

    protected function setUp(): void
    {
        $this->userIntentFactoryRegistry = $this->createMock(UserIntentFactoryRegistry::class);
        $this->sut = new StandardFormatToUserIntents($this->userIntentFactoryRegistry);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(StandardFormatToUserIntents::class, $this->sut);
        $this->assertInstanceOf(StandardFormatToUserIntentsInterface::class, $this->sut);
    }

    public function testItMergesTheUserIntentsOfEveryFieldIntoAFlatListInFieldOrder(): void
    {
        $setEnLabel = new SetLabel('en_US', 'socks');
        $setFrLabel = new SetLabel('fr_FR', 'chaussettes');
        $setText = new SetText('a_uuid', 'a_code', null, 'en_US', 'a text value');

        $this->userIntentFactoryRegistry
            ->method('fromStandardFormatField')
            ->willReturnCallback(static fn(string $fieldName): array => match ($fieldName) {
                'labels' => [$setEnLabel, $setFrLabel],
                'values' => [$setText],
                default => [],
            });

        $result = $this->sut->convert([
            'id' => 42,
            'labels' => [
                'en_US' => 'socks',
                'fr_FR' => 'chaussettes',
            ],
            'values' => [
                'a_code|a_uuid|en_US' => [
                    'data' => 'a text value',
                    'locale' => 'en_US',
                    'attribute_code' => 'a_code|a_uuid',
                ],
            ],
        ]);

        $this->assertSame([$setEnLabel, $setFrLabel, $setText], $result);
    }

    public function testItForwardsEveryFieldToTheRegistryWithTheCategoryIdReadFromTheIdField(): void
    {
        $calls = [];

        $this->userIntentFactoryRegistry
            ->expects($this->exactly(3))
            ->method('fromStandardFormatField')
            ->willReturnCallback(
                function (string $fieldName, int $categoryId, mixed $data) use (&$calls): array {
                    $calls[] = [$fieldName, $categoryId, $data];

                    return [];
                }
            );

        $this->sut->convert([
            'id' => 42,
            'code' => 'socks',
            'labels' => ['en_US' => 'socks'],
        ]);

        // The 'id' field is forwarded like any other field (the registry is the one ignoring it),
        // its value is the category id given to every call, and each field's data is passed untouched.
        $this->assertSame(
            [
                ['id', 42, 42],
                ['code', 42, 'socks'],
                ['labels', 42, ['en_US' => 'socks']],
            ],
            $calls
        );
    }

    public function testItRemovesTheEmptyUserIntentsReturnedByTheRegistryAndKeepsTheRemainingKeys(): void
    {
        $setLabel = new SetLabel('en_US', 'socks');

        $this->userIntentFactoryRegistry
            ->method('fromStandardFormatField')
            ->willReturnCallback(static fn(string $fieldName): array => match ($fieldName) {
                'labels' => [null, $setLabel],
                default => [],
            });

        $result = $this->sut->convert([
            'id' => 42,
            'labels' => ['en_US' => 'socks'],
        ]);

        // array_filter() drops the empty entries but does not reindex: the surviving intent keeps its key.
        $this->assertSame([1 => $setLabel], $result);
    }

    public function testItReturnsAnEmptyListWhenNoFieldProducesAnyUserIntent(): void
    {
        $this->userIntentFactoryRegistry
            ->expects($this->exactly(2))
            ->method('fromStandardFormatField')
            ->willReturn([]);

        $this->assertSame([], $this->sut->convert([
            'id' => 42,
            'code' => 'socks',
        ]));
    }

    public function testItStopsTheConversionAndPropagatesTheExceptionRaisedByTheRegistry(): void
    {
        $processedFields = [];

        $this->userIntentFactoryRegistry
            ->method('fromStandardFormatField')
            ->willReturnCallback(
                function (string $fieldName) use (&$processedFields): array {
                    $processedFields[] = $fieldName;
                    if ('foobar' === $fieldName) {
                        throw new \InvalidArgumentException(
                            \sprintf('Cannot create userIntent from %s fieldName', $fieldName)
                        );
                    }

                    return [];
                }
            );

        try {
            $this->sut->convert([
                'id' => 42,
                'foobar' => 'foo',
                'labels' => ['en_US' => 'socks'],
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('Cannot create userIntent from foobar fieldName', $exception->getMessage());
            // The conversion fails fast: the fields declared after the faulty one are never converted.
            $this->assertSame(['id', 'foobar'], $processedFields);
        }
    }
}

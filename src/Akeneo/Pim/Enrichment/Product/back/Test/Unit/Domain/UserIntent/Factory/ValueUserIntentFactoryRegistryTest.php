<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetAttributeTypes;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\UserIntentFactory;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValueUserIntentFactory;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\ValueUserIntentFactoryRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueUserIntentFactoryRegistryTest extends TestCase
{
    private GetAttributeTypes|MockObject $getAttributeTypes;
    private ValueUserIntentFactory|MockObject $valueUserIntentFactory1;
    private ValueUserIntentFactory|MockObject $valueUserIntentFactory2;
    private ValueUserIntentFactory|MockObject $valueUserIntentFactory3;
    private ValueUserIntentFactoryRegistry $sut;

    protected function setUp(): void
    {
        $this->getAttributeTypes = $this->createMock(GetAttributeTypes::class);
        $this->valueUserIntentFactory1 = $this->createMock(ValueUserIntentFactory::class);
        $this->valueUserIntentFactory2 = $this->createMock(ValueUserIntentFactory::class);
        $this->valueUserIntentFactory3 = $this->createMock(ValueUserIntentFactory::class);
        $this->valueUserIntentFactory1->method('getSupportedAttributeTypes')->willReturn(['pim_catalog_text']);
        $this->valueUserIntentFactory2->method('getSupportedAttributeTypes')->willReturn(['pim_catalog_identifier']);
        $this->valueUserIntentFactory3->method('getSupportedAttributeTypes')->willReturn(['pim_catalog_textarea']);
        $this->sut = new ValueUserIntentFactoryRegistry($this->getAttributeTypes, [$this->valueUserIntentFactory1, $this->valueUserIntentFactory2, $this->valueUserIntentFactory3]);
        $this->assertInstanceOf(UserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_user_intents(): void
    {
        $valueUserIntent1 = $this->createMock(ValueUserIntent::class);
        $valueUserIntent2 = $this->createMock(ValueUserIntent::class);
        $valueUserIntent3 = $this->createMock(ValueUserIntent::class);
        $valueUserIntent4 = $this->createMock(ValueUserIntent::class);

        $this->getAttributeTypes->expects($this->once())->method('fromAttributeCodes')->with(['a_text', 'sku', 'A_TExtAreA', '1234'])->willReturn([
                        'a_text' => 'pim_catalog_text',
                        'sku' => 'pim_catalog_identifier',
                        'A_TExtAreA' => 'pim_catalog_textarea',
                        '1234' => 'pim_catalog_textarea',
                    ]);
        $this->valueUserIntentFactory1->expects($this->once())->method('create')->with('pim_catalog_text', 'a_text', ['data' => 'bonjour', 'locale' => null, 'scope' => null])->willReturn($valueUserIntent1);
        $this->valueUserIntentFactory2->expects($this->once())->method('create')->with('pim_catalog_identifier', 'sku', ['data' => 'my_sku'])->willReturn($valueUserIntent2);
        $this->valueUserIntentFactory3->expects($this->exactly(2))->method('create')
            ->willReturnCallback(function (string $type, string $code, array $data) use ($valueUserIntent3, $valueUserIntent4) {
                if ($code === 'A_TExtAreA') {
                    return $valueUserIntent3;
                }
                if ($code === '1234') {
                    return $valueUserIntent4;
                }
                return null;
            });
        $this->assertSame([$valueUserIntent1, $valueUserIntent2, $valueUserIntent3, $valueUserIntent4], $this->sut->create('values', [
                    'a_text' => [['data' => 'bonjour', 'locale' => null, 'scope' => null]],
                    'sku' => [['data' => 'my_sku']],
                    'A_TExtAreA' => [['data' => '<p>bonjour</p>', 'locale' => null, 'scope' => null]],
                    1234 => [['data' => 'some content', 'locale' => null, 'scope' => null]],
                ]));
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory\Value;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\Value\MeasurementValueUserIntentFactory;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class MeasurementValueUserIntentFactoryTest extends TestCase
{
    private MeasurementValueUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new MeasurementValueUserIntentFactory();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MeasurementValueUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_set_measurement_user_intent(): void
    {
        $this->assertEquals(new SetMeasurementValue('a_metric', null, null, 20, 'KILOMETER'), $this->sut->create(AttributeTypes::METRIC, 'a_metric', [
                    'data' => [
                        'amount' => 20,
                        'unit' => 'KILOMETER',
                    ],
                    'locale' => null,
                    'scope' => null,
                ]));
    }

    public function test_it_returns_clear_value(): void
    {
        $this->assertEquals(new ClearValue('a_metric', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::METRIC, 'a_metric', [
                    'data' => null,
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
        $this->assertEquals(new ClearValue('a_metric', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::METRIC, 'a_metric', [
                    'data' => '',
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
        $this->assertEquals(new ClearValue('a_metric', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::METRIC, 'a_metric', [
                    'data' => ['amount' => null, 'unit' => 'KILOMETER'],
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
        $this->assertEquals(new ClearValue('a_metric', 'ecommerce', 'fr_FR'), $this->sut->create(AttributeTypes::METRIC, 'a_metric', [
                    'data' => ['amount' => '', 'unit' => 'KILOMETER'],
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                ]));
    }

    public function test_it_throws_an_exception_if_data_is_not_valid(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['value']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => 'coucou', 'locale' => 'fr_FR']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => 'coucou', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => [], 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => ['amount' => 20], 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create(AttributeTypes::METRIC, 'a_metric', ['data' => ['unit' => 'KILOMETER'], 'locale' => 'fr_FR', 'scope' => 'ecommerce']);
    }
}

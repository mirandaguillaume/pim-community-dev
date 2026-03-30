<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Controller\ExternalApi\JsonSchemaErrorsFormatter;
use PHPUnit\Framework\TestCase;

class JsonSchemaErrorsFormatterTest extends TestCase
{
    private JsonSchemaErrorsFormatter $sut;

    protected function setUp(): void
    {
        $this->sut = new JsonSchemaErrorsFormatter();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(JsonSchemaErrorsFormatter::class, $this->sut);
    }

    public function test_it_maps_only_mandatory_properties(): void
    {
        $errors = [
                    [
                        'property' => '/property1',
                        'message' => 'wrong type',
                        'additionalProperty' => 'some additional error description',
                    ],
                ];
        $formattedErrors = $this::format($errors);
        $formattedErrors->shouldBeArray();
        $formattedErrors->shouldHaveCount(1);
        $formattedErrors[0]->shouldHaveKey('property');
        $formattedErrors[0]->shouldHaveKey('message');
        $formattedErrors[0]->shouldNotHaveKey('additionalProperty');
    }

    public function test_it_maps_properties_with_default_values(): void
    {
        $errors = [
                    [
                        'property' => '/property1',
                    ],
                    [
                        'message' => 'wrong type',
                    ],
                ];
        $formattedErrors = $this::format($errors);
        $formattedErrors->shouldBeArray();
        $formattedErrors->shouldHaveCount(2);
        $formattedErrors[0]->shouldHaveKeyWithValue('property', 'property1');
        $formattedErrors[0]->shouldHaveKeyWithValue('message', '');
        $formattedErrors[1]->shouldHaveKeyWithValue('property', '');
        $formattedErrors[1]->shouldHaveKeyWithValue('message', 'wrong type');
    }

    public function test_it_converts_opis_property_paths(): void
    {
        $errors = [
                    [
                        'property' => '/property1/property2/1/property3',
                    ],
                ];
        $formattedErrors = $this::format($errors);
        $formattedErrors[0]->shouldHaveKeyWithValue('property', 'property1.property2[1].property3');
    }
}

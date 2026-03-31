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
        $formattedErrors = JsonSchemaErrorsFormatter::format($errors);
        $this->assertIsArray($formattedErrors);
        $this->assertCount(1, $formattedErrors);
        $this->assertArrayHasKey('property', $formattedErrors[0]);
        $this->assertArrayHasKey('message', $formattedErrors[0]);
        $this->assertArrayNotHasKey('additionalProperty', $formattedErrors[0]);
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
        $formattedErrors = JsonSchemaErrorsFormatter::format($errors);
        $this->assertIsArray($formattedErrors);
        $this->assertCount(2, $formattedErrors);
        $this->assertSame('property1', $formattedErrors[0]['property']);
        $this->assertSame('', $formattedErrors[0]['message']);
        $this->assertSame('', $formattedErrors[1]['property']);
        $this->assertSame('wrong type', $formattedErrors[1]['message']);
    }

    public function test_it_converts_opis_property_paths(): void
    {
        $errors = [
            [
                'property' => '/property1/property2/1/property3',
            ],
        ];
        $formattedErrors = JsonSchemaErrorsFormatter::format($errors);
        $this->assertSame('property1.property2[1].property3', $formattedErrors[0]['property']);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error\Documentation;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\MessageParameterInterface;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\MessageParameterTypes;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use PHPUnit\Framework\TestCase;

class RouteMessageParameterTest extends TestCase
{
    private RouteMessageParameter $sut;

    protected function setUp(): void
    {
        $this->sut = new RouteMessageParameter('Attributes settings',
            'pim_enrich_attribute_index');
    }

    public function test_it_is_a_route_message_parameter(): void
    {
        $this->assertInstanceOf(RouteMessageParameter::class, $this->sut);
        $this->assertInstanceOf(MessageParameterInterface::class, $this->sut);
    }

    public function test_it_normalizes_information(): void
    {
        $this->assertSame([
                    'type' => MessageParameterTypes::ROUTE,
                    'route' => 'pim_enrich_attribute_index',
                    'routeParameters' => [],
                    'title' => 'Attributes settings',
                ], $this->sut->normalize());
    }

    public function test_it_validates_that_the_route_has_the_good_format(): void
    {
        $wrongMatches = [
                    'pim_enrich_attribute_index ',
                    'pim enrich attribute index',
                    'pim_enrich_attribute_index123',
                    '{pim_enrich_attribute_index}',
                ];
        foreach ($wrongMatches as $wrongMatch) {
            try {
                new RouteMessageParameter(
                    'Attributes settings',
                    $wrongMatch
                );
                $this->fail('Expected InvalidArgumentException was not thrown for: ' . $wrongMatch);
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString($wrongMatch, $e->getMessage());
            }
        }
    }

    public function test_it_validates_that_the_route_parameters_have_the_good_format(): void
    {
        $wrongMatches = [
                    ['12'],
                    ['code' => 'pastel', 'zero'],
                    ['code' => 'pastel', 'zero' => []],
                ];
        foreach ($wrongMatches as $wrongMatch) {
            try {
                new RouteMessageParameter(
                    'Attributes settings',
                    'pim_enrich_attribute_index',
                    $wrongMatch
                );
                $this->fail('Expected InvalidArgumentException was not thrown');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString(RouteMessageParameter::class, $e->getMessage());
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Error\Documentation;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\MessageParameterInterface;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\MessageParameterTypes;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use PHPUnit\Framework\TestCase;

class DocumentationTest extends TestCase
{
    private Documentation $sut;

    protected function setUp(): void
    {
    }

    public function test_it_normalizes_the_documentation(): void
    {
        $this->sut = new Documentation('More information about attributes: {what_is_attribute} {attribute_settings}.',
                    [
                        'what_is_attribute' => new HrefMessageParameter(
                            'What is an attribute?',
                            'https://help.akeneo.com/what-is-an-attribute.html'
                        ),
                        'attribute_settings' => new RouteMessageParameter(
                            'Attributes settings',
                            'pim_enrich_attribute_index'
                        )
                    ],
                    Documentation::STYLE_TEXT);
        $this->assertSame([
                    'message' => 'More information about attributes: {what_is_attribute} {attribute_settings}.',
                    'parameters' => [
                        'what_is_attribute' => [
                            'type' => MessageParameterTypes::HREF,
                            'href' => 'https://help.akeneo.com/what-is-an-attribute.html',
                            'title' => 'What is an attribute?',
                        ],
                        'attribute_settings' => [
                            'type' => MessageParameterTypes::ROUTE,
                            'route' => 'pim_enrich_attribute_index',
                            'routeParameters' => [],
                            'title' => 'Attributes settings',
                        ],
                    ],
                    'style' => Documentation::STYLE_TEXT
                ], $this->sut->normalize());
    }

    public function test_it_validates_that_message_parameters_implement_the_good_interface(): void
    {
        $this->expectException(new \InvalidArgumentException(sprintf(
                            'Class "%s" accepts only associative array of "%s" as $messageParameters.',
                            Documentation::class,
                            MessageParameterInterface::class
                        )));
        new Documentation('More information about attributes: {what_is_attribute} {attribute_settings}.',
                    [
                        'what_is_attribute' => new HrefMessageParameter(
                            'What is an attribute?',
                            'https://help.akeneo.com/what-is-an-attribute.html'
                        ),
                        'anything' => new class ()
                        {
                        }
                    ],
                    Documentation::STYLE_TEXT);
    }

    public function test_it_validates_that_message_parameters_provided_match_parameters_from_message(): void
    {
        $message = 'More information about attributes: {what_is_attribute} {attribute_settings}.';
        foreach (['what_attribute', '{what_is_attribute}'] as $wrongMatch) {
                    $this->beConstructedWith(
                        $message,
                        [
                            $wrongMatch => new HrefMessageParameter(
                                'What is an attribute?',
                                'https://help.akeneo.com/what-is-an-attribute.html'
                            ),
                        ],
                        Documentation::STYLE_TEXT
                    );

                    $this
                        ->shouldThrow(
                            new \InvalidArgumentException(sprintf(
                                '$messageParameters "%s" not found in $message "%s".',
                                $wrongMatch,
                                $message
                            ))
                        )
                        ->duringInstantiation();
                }
        $this->expectException(new \InvalidArgumentException(sprintf(
                            '$messageParameters "%s" not found in $message "%s".',
                            0,
                            $message
                        )));
        new Documentation($message,
                    [
                        new HrefMessageParameter(
                            'What is an attribute?',
                            'https://help.akeneo.com/what-is-an-attribute.html'
                        ),
                    ],
                    Documentation::STYLE_TEXT);
    }

    public function test_it_validates_the_documentation_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Documentation('More information about attributes!',
                    [],
                    'wrong_documentation_type');
    }
}

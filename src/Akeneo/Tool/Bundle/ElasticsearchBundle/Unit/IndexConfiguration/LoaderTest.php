<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration;

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LoaderTest extends TestCase
{
    private ParameterBagInterface|MockObject $parameterBag;
    private Loader $sut;

    protected function setUp(): void
    {
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->parameterBag->method('resolveValue')->with($this->anything());
    }

    public function test_it_loads_the_configuration_from_a_single_file(): void
    {
        $this->sut = new Loader([__DIR__ . '/conf1.yml'], $this->parameterBag);
        $indexConfiguration = $this->load();
        $indexConfiguration->getSettings()->shouldReturn(
            [
                        'analysis' => [
                            'char_filter' => [
                                'newline_pattern' => [
                                    'type' => 'pattern_replace',
                                    'pattern' => '\\n',
                                    'replacement' => '',
                                ],
                            ],
                        ],
                    ]
        );
        $indexConfiguration->getMappings()->shouldReturn(
            [
                        'properties' => [
                            'name' => [
                                'properties' => [
                                    'last' => [
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                            'user_id' => [
                                'type' => 'keyword',
                                'ignore_above' => 100,
                            ],
                        ],
                        'dynamic_templates' => [
                            [
                                'my_dynamic_template_1' => [
                                    'path_match' => '*foo*',
                                    'match_mapping_type' => 'object',
                                    'mapping'
                                        => [
                                            'type' => 'object',
                                        ],
                                ],
                            ],
                            [
                                'my_dynamic_template_2' => [
                                    'path_match' => '*bar*',
                                    'mapping'
                                        => [
                                            'type' => 'keyword',
                                            'index' => 'not_analyzed',
                                        ],
                                ],
                            ],
                        ],
                    ]
        );
        $indexConfiguration->getAliases()->shouldReturn([]);
        $indexConfiguration->getHash()->shouldReturn('ba2c495be83ae33df74fe96f9df1cfc305fe983e');
    }

    public function test_it_loads_the_configuration_from_multiple_files(): void
    {
        $this->sut = new Loader(
            [
                        __DIR__ . '/conf1.yml',
                        __DIR__ . '/conf2.yml',
                        __DIR__ . '/conf3.yml',
                    ],
            $this->parameterBag
        );
        $indexConfiguration = $this->load();
        $indexConfiguration->getSettings()->shouldReturn(
            [
                        'analysis' => [
                            'char_filter' => [
                                'newline_pattern' => [
                                    'type' => 'pattern_replace',
                                    'pattern' => '\\n',
                                    'replacement' => '',
                                ],
                            ],
                        ],
                        'index' => [
                            'number_of_shards' => 3,
                            'number_of_replicas' => 2,
                        ],
                    ]
        );
        $indexConfiguration->getMappings()->shouldReturn(
            [
                        'properties' => [
                            'name' => [
                                'properties' => [
                                    'last' => [
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                            'user_id' => [
                                'type' => 'keyword',
                                'ignore_above' => 100,
                            ],
                            'just_another_property' => null,
                        ],
                        'dynamic_templates' => [
                            [
                                'my_dynamic_template_1' => [
                                    'path_match' => '*foo*',
                                    'match_mapping_type' => 'object',
                                    'mapping' => [
                                        'type' => 'object',
                                    ],
                                ],
                            ],
                            [
                                'my_dynamic_template_2' => [
                                    'path_match' => '*bar*',
                                    'mapping' => [
                                        'type' => 'keyword',
                                        'index' => 'not_analyzed',
                                    ],
                                ],
                            ],
                            [
                                'my_dynamic_template_3' => [
                                    'path_match' => '*foo3*',
                                    'match_mapping_type' => 'object',
                                    'mapping' => [
                                        'type' => 'object',
                                    ],
                                ],
                            ],
                        ],
                        'just_another_mapping' => null,
                    ]
        );
        $indexConfiguration->getAliases()->shouldReturn(
            [
                        'alias_1' => [],
                        'alias_2' => [
                            'filter' => [
                                'term' => [
                                    'user' => 'kimchy',
                                ],
                            ],
                            'routing' => 'kimchy',
                        ],
                    ]
        );
        $indexConfiguration->getHash()->shouldReturn('774d394edb20f41c507d91792744036301532946');
    }

    public function test_it_loads_the_compiled_configuration_from_multiple_files(): void
    {
        $this->sut = new Loader(
            [
                        __DIR__ . '/conf1.yml',
                        __DIR__ . '/conf2.yml',
                        __DIR__ . '/conf3.yml',
                    ],
            $this->parameterBag
        );
        $indexConfiguration = $this->load();
        $indexConfiguration->buildAggregated()->shouldReturn([
                    'settings'
                        => [
                            'analysis' => [
                                'char_filter' => [
                                    'newline_pattern' => [
                                        'type' => 'pattern_replace',
                                        'pattern' => '\\n',
                                        'replacement' => '',
                                    ],
                                ],
                            ],
                            'index' => [
                                'number_of_shards' => 3,
                                'number_of_replicas' => 2,
                            ],
                        ],
                    'mappings'
                        => [
                            'properties' => [
                                'name' => [
                                    'properties' => [
                                        'last' => [
                                            'type' => 'text',
                                        ],
                                    ],
                                ],
                                'user_id' => [
                                    'type' => 'keyword',
                                    'ignore_above' => 100,
                                ],
                                'just_another_property' => null,
                            ],
                            'dynamic_templates' => [
                                [
                                    'my_dynamic_template_1' => [
                                        'path_match' => '*foo*',
                                        'match_mapping_type' => 'object',
                                        'mapping'
                                            => [
                                                'type' => 'object',
                                            ],
                                    ],
                                ],
                                [
                                    'my_dynamic_template_2' => [
                                        'path_match' => '*bar*',
                                        'mapping'
                                            => [
                                                'type' => 'keyword',
                                                'index' => 'not_analyzed',
                                            ],
                                    ],
                                ],
                                [
                                    'my_dynamic_template_3' => [
                                        'path_match' => '*foo3*',
                                        'match_mapping_type' => 'object',
                                        'mapping' => [
                                            'type' => 'object',
                                        ],
                                    ],
                                ],
                            ],
                            'just_another_mapping' => null,
                        ],
                    'aliases'
                        => [
                            'alias_1' => [],
                            'alias_2' => [
                                'filter' => [
                                    'term' => [
                                        'user' => 'kimchy',
                                    ],
                                ],
                                'routing' => 'kimchy',
                            ],
                        ],
                ]);
    }

    public function test_it_replaces_parameters_in_the_configuration(): void
    {
        $this->sut = new Loader(
            [
                        __DIR__ . '/conf4.yml',
                    ],
            $this->parameterBag
        );
        $this->parameterBag->method('resolveValue')->with('%elasticsearch_total_fields_limit%')->willReturn(10000);
        $indexConfiguration = $this->load();
        $indexConfiguration->getSettings()->shouldReturn(
            [
                        'analysis' => [
                            'char_filter' => [
                                'newline_pattern' => [
                                    'type' => 'pattern_replace',
                                    'pattern' => '\\n',
                                    'replacement' => '',
                                ],
                            ],
                        ],
                        'mapping' => [
                            'total_fields' => [
                                'limit' => 10000,
                            ],
                        ],
                    ]
        );
    }

    public function test_it_throws_an_exception_when_a_file_is_not_readable(): void
    {
        $this->sut = new Loader(
            [
                        __DIR__ . '/conf1.yml',
                        __DIR__ . '/do_not_exists.yml',
                        __DIR__ . '/conf2.yml',
                    ],
            $this->parameterBag
        );
        $this->sut->shouldThrow('\Exception')->during('load');
    }
}

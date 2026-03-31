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
        $this->parameterBag->method('resolveValue')->with($this->anything())->willReturnArgument(0);
    }

    public function test_it_loads_the_configuration_from_a_single_file(): void
    {
        $this->sut = new Loader([__DIR__ . '/conf1.yml'], $this->parameterBag);
        $indexConfiguration = $this->sut->load();
        $this->assertSame(
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
            ],
            $indexConfiguration->getSettings()
        );
        $this->assertSame(
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
                ],
            ],
            $indexConfiguration->getMappings()
        );
        $this->assertSame([], $indexConfiguration->getAliases());
        $this->assertSame('ba2c495be83ae33df74fe96f9df1cfc305fe983e', $indexConfiguration->getHash());
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
        $indexConfiguration = $this->sut->load();
        $this->assertSame(
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
            ],
            $indexConfiguration->getSettings()
        );
        $this->assertSame(
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
            ],
            $indexConfiguration->getAliases()
        );
        $this->assertSame('774d394edb20f41c507d91792744036301532946', $indexConfiguration->getHash());
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
        $indexConfiguration = $this->sut->load();
        $aggregated = $indexConfiguration->buildAggregated();
        $this->assertArrayHasKey('settings', $aggregated);
        $this->assertArrayHasKey('mappings', $aggregated);
        $this->assertArrayHasKey('aliases', $aggregated);
    }

    public function test_it_replaces_parameters_in_the_configuration(): void
    {
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->parameterBag->method('resolveValue')->willReturnCallback(function ($value) {
            if ($value === '%elasticsearch_total_fields_limit%') {
                return 10000;
            }
            return $value;
        });
        $this->sut = new Loader(
            [__DIR__ . '/conf4.yml'],
            $this->parameterBag
        );
        $indexConfiguration = $this->sut->load();
        $settings = $indexConfiguration->getSettings();
        $this->assertSame(10000, $settings['mapping']['total_fields']['limit']);
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
        $this->expectException(\Exception::class);
        $this->sut->load();
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\MissingIdentifierException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Namespaces\IndicesNamespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private Client|MockObject $client;
    private ClientBuilder|MockObject $clientBuilder;
    private Loader|MockObject $indexConfigurationLoader;
    private Client $sut;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->clientBuilder = $this->createMock(ClientBuilder::class);
        $this->indexConfigurationLoader = $this->createMock(Loader::class);
        $this->sut = new Client($this->clientBuilder, $this->indexConfigurationLoader, ['localhost:9200'], 'an_index_name');
        $this->clientBuilder->method('setHosts')->with($this->anything())->willReturn($this->clientBuilder);
        $this->clientBuilder->method('build')->willReturn($this->client);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Client::class, $this->sut);
    }

    public function test_it_indexes_a_document(): void
    {
        $this->client->method('index')->with([
                        'index' => 'an_index_name',
                        'id' => 'identifier',
                        'body' => ['a key' => 'a value'],
                        'refresh' => 'wait_for',
                    ])->willReturn(['errors' => false]);
        $this->sut->index('identifier', ['a key' => 'a value'], Refresh::waitFor());
    }

    public function test_it_triggers_an_exception_during_the_indexation_of_a_document(): void
    {
        $this->client->method('index')->with($this->anything())->willThrowException(\Exception::class);
        $this->expectException(IndexationException::class);
        $this->sut->index('identifier', ['a key' => 'a value'], Refresh::waitFor());
    }

    public function test_it_triggers_an_exception_if_the_indexation_of_a_document_has_failed(): void
    {
        $this->client->method('index')->with($this->isType('array'))->willReturn([
                        'errors' => true,
                        'items' => [
                            ['index' => ['error' => 'foo']],
                        ],
                    ]);
        $this->expectException(IndexationException::class);
        $this->sut->index('identifier', ['a document'], Refresh::waitFor());
    }

    public function test_it_gets_a_document(): void
    {
        $this->client->expects($this->once())->method('get')->with([
                        'index' => 'an_index_name',
                        'id' => 'identifier',
                    ]);
        $this->sut->get('identifier');
    }

    public function test_it_searches_documents(): void
    {
        $this->client->expects($this->once())->method('search')->with([
                        'index' => 'an_index_name',
                        'body' => ['a key' => 'a value'],
                    ]);
        $this->sut->search(['a key' => 'a value']);
    }

    public function test_it_counts_documents(): void
    {
        $this->client->expects($this->once())->method('count')->with([
                        'index' => 'an_index_name',
                        'body' => ['query' => 'some_query'],
                    ])->willReturn(['count' => 42]);
        $this->assertSame(['count' => 42], $this->sut->count(['query' => 'some_query']));
    }

    public function test_it_multi_searches_documents(): void
    {
        $this->client->method('msearch')->with([
                        'index' => 'an_index_name',
                        'body' => [
                            ['index' => 'another_index_name'],
                            ['size' => 0, 'query' => ['match_all' => (object) []]],
                            [],
                            ['size' => 0, 'query' => ['match_all' => (object) []]],
                        ],
                    ])->willReturn([
                    [
                        'took' => 51,
                        'timed_out' => false,
                        '_shards' => [
                            'total' => 5,
                            'successful' => 5,
                            'failed' => 0,
                        ],
                        [
                            'took' => 53,
                            'timed_out' => false,
                            '_shards' => [
                                'total' => 7,
                                'successful' => 5,
                                'failed' => 0,
                            ],
                        ],
                    ],
                ]);
        $this->assertSame([
                    [
                        'took' => 51,
                        'timed_out' => false,
                        '_shards' => [
                            'total' => 5,
                            'successful' => 5,
                            'failed' => 0,
                        ],
                        [
                            'took' => 53,
                            'timed_out' => false,
                            '_shards' => [
                                'total' => 7,
                                'successful' => 5,
                                'failed' => 0,
                            ],
                        ],
                    ],
                ], $this->sut->msearch([
                    ['index' => 'another_index_name'],
                    ['size' => 0, 'query' => ['match_all' => (object) []]],
                    [],
                    ['size' => 0, 'query' => ['match_all' => (object) []]],
                ]));
    }

    public function test_it_deletes_a_document(): void
    {
        $this->client->expects($this->once())->method('delete')->with([
                        'index' => 'an_index_name',
                        'id' => 'identifier',
                    ]);
        $this->sut->delete('identifier');
    }

    public function test_it_bulk_deletes_documents(): void
    {
        $this->client->expects($this->once())->method('bulk')->with([
                        'body' => [
                            [
                                'delete' => [
                                    '_index' => 'an_index_name',
                                    '_id' => 40,
                                ],
                            ],
                            [
                                'delete' => [
                                    '_index' => 'an_index_name',
                                    '_id' => 33,
                                ],
                            ],
                        ],
                    ]);
        $this->sut->bulkDelete([40, 33]);
    }

    public function test_it_bulk_updates_documents(): void
    {
        $this->client->expects($this->once())->method('bulk')->with([
                        'body' => [
                            [
                                'update' => [
                                    '_index' => 'an_index_name',
                                    '_id' => '40',
                                ],
                            ],
                            'params_of_id_40',
                            [
                                'update' => [
                                    '_index' => 'an_index_name',
                                    '_id' => '33',
                                ],
                            ],
                            'params_of_id_33',
                        ],
                    ]);
        $this->sut->bulkUpdate(['40', '33'], ['40' => 'params_of_id_40', '33' => 'params_of_id_33']);
    }

    public function test_it_deletes_an_index_without_alias(): void
    {
        $indices = $this->createMock(IndicesNamespace::class);

        $this->client->method('indices')->willReturn($indices);
        $indices->method('existsAlias')->with(['name' => 'an_index_name'])->willReturn(false);
        $indices->expects($this->once())->method('delete')->with(['index' => 'an_index_name']);
        $this->sut->deleteIndex();
    }

    public function test_it_deletes_an_index_with_alias(): void
    {
        $indices = $this->createMock(IndicesNamespace::class);

        $this->client->method('indices')->willReturn($indices);
        $indices->method('existsAlias')->with(['name' => 'an_index_name'])->willReturn(true);
        $expectedAlias = [
                    'an_index_name_foo_20190514' => [
                        'an_index_name' => ['index_data'],
                    ],
                ];
        $indices->method('getAlias')->with(['name' => 'an_index_name'])->willReturn($expectedAlias);
        $indices->expects($this->once())->method('delete')->with(['index' => 'an_index_name_foo_20190514']);
        $this->sut->deleteIndex();
    }

    public function test_it_checks_if_an_index_exists(): void
    {
        $indices = $this->createMock(IndicesNamespace::class);

        $this->client->method('indices')->willReturn($indices);
        $indices->method('exists')->with(['index' => 'an_index_name'])->willReturn(true);
        $this->assertSame(true, $this->sut->hasIndex());
    }

    public function test_it_checks_if_an_alias_exists(): void
    {
        $indices = $this->createMock(IndicesNamespace::class);

        $this->client->method('indices')->willReturn($indices);
        $indices->method('existsAlias')->with(['name' => 'an_index_name'])->willReturn(true);
        $this->assertSame(true, $this->sut->hasIndexForAlias());
    }

    public function test_it_refreshes_an_index(): void
    {
        $indices = $this->createMock(IndicesNamespace::class);

        $this->client->method('indices')->willReturn($indices);
        $indices->expects($this->once())->method('refresh')->with(['index' => 'an_index_name']);
        $this->sut->refreshIndex();
    }

    public function test_it_indexes_with_bulk_several_documents(): void
    {
        $expectedResponse = [
                    'took' => 1,
                    'errors' => false,
                    'items' => [
                        ['item_foo'],
                        ['item_bar'],
                    ],
                ];
        $this->client->expects($this->once())->method('bulk')->with([
                        'body' => [
                            ['index' => [
                                '_index' => 'an_index_name',
                                '_id' => 'foo',
                            ]],
                            ['identifier' => 'foo', 'name' => 'a name'],
                            ['index' => [
                                '_index' => 'an_index_name',
                                '_id' => 'bar',
                            ]],
                            ['identifier' => 'bar', 'name' => 'a name'],
                        ],
                        'refresh' => 'wait_for',
                    ])->willReturn($expectedResponse);
        ;
        $documents = [
                    ['identifier' => 'foo', 'name' => 'a name'],
                    ['identifier' => 'bar', 'name' => 'a name'],
                ];
        $this->assertSame($expectedResponse, $this->sut->bulkIndexes($documents, 'identifier', Refresh::waitFor()));
    }

    public function test_it_split_bulk_index_when_size_is_more_than_max_batch_size(): void
    {
        $this->sut = new Client($this->clientBuilder, $this->indexConfigurationLoader, ['localhost:9200'], 'an_index_name', '', 200);
        $this->client->expects($this->exactly(1))->method('bulk')->with([
                    'body' => [
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value1']],
                        ['identifier' => 'value1', 'name' => 'name1'],
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value2']],
                        ['identifier' => 'value2', 'name' => 'name2'],
                    ],
                    'refresh' => 'wait_for',
                ])->willReturn([
                    'took' => 1,
                    'errors' => false,
                    'items' => [
                        ['item_value1'],
                        ['item_value2'],
                    ],
                ]);
        $this->client->expects($this->exactly(1))->method('bulk')->with([
                    'body' => [
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value3']],
                        ['identifier' => 'value3', 'name' => 'name3'],
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value4']],
                        ['identifier' => 'value4', 'name' => 'name4'],
                    ],
                    'refresh' => 'wait_for',
                ])->willReturn([
                    'took' => 1,
                    'errors' => false,
                    'items' => [
                        ['item_value3'],
                        ['item_value4'],
                    ],
                ]);
        $this->client->expects($this->exactly(1))->method('bulk')->with([
                    'body' => [
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value5']],
                        ['identifier' => 'value5', 'name' => 'name5'],
                    ],
                    'refresh' => 'wait_for',
                ])->willReturn([
                    'took' => 1,
                    'errors' => false,
                    'items' => [
                        ['item_value5'],
                    ],
                ]);
        $documents = [
                    ['identifier' => 'value1', 'name' => 'name1'],
                    ['identifier' => 'value2', 'name' => 'name2'],
                    ['identifier' => 'value3', 'name' => 'name3'],
                    ['identifier' => 'value4', 'name' => 'name4'],
                    ['identifier' => 'value5', 'name' => 'name5'],
                ];
        $this->assertSame([
                    'took' => 3,
                    'errors' => false,
                    'items' => [
                        ['item_value1'],
                        ['item_value2'],
                        ['item_value3'],
                        ['item_value4'],
                        ['item_value5'],
                    ],
                ], $this->sut->bulkIndexes($documents, 'identifier', Refresh::waitFor()));
    }

    public function test_it_retries_bulk_index_request_when_an_error_occurred(): void
    {
        $isFirstCall = true;
        // TODO: manual conversion needed — complex .will() callback
        // $client->bulk([
        //             'body' => [
        //                 ['index' => ['_index' => 'an_index_name', '_id' => 'value1']],
        //                 ['identifier' => 'value1', 'name' => 'name1'],
        //             ],
        //             'refresh' => 'wait_for',
        //         ])
        //         ->shouldBeCalledTimes(2)
        //         ->will(function () use (&$isFirstCall) {
        //             if ($isFirstCall) {
        //                 $isFirstCall = false;
        //                 throw new BadRequest400Exception();
        //             }
        //
        //             return ['took' => 1, 'errors' => false, 'items' => [['item_value1']]];
        //         });
        $documents = [['identifier' => 'value1', 'name' => 'name1']];
        $this->assertSame([
                    'took' => 1,
                    'errors' => false,
                    'items' => [
                        ['item_value1'],
                    ],
                ], $this->sut->bulkIndexes($documents, 'identifier', Refresh::waitFor()));
    }

    public function test_it_retries_bulk_index_request_by_splitting_body_when_an_error_occurred(): void
    {
        $this->client->method('bulk')->with([
                    'body' => [
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value1']],
                        ['identifier' => 'value1', 'name' => 'name1'],
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value2']],
                        ['identifier' => 'value2', 'name' => 'name2'],
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value3']],
                        ['identifier' => 'value3', 'name' => 'name3'],
                    ],
                    'refresh' => 'wait_for',
                ])->willThrowException(BadRequest400Exception::class);
        $this->client->expects($this->exactly(1))->method('bulk')->with([
                    'body' => [
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value1']],
                        ['identifier' => 'value1', 'name' => 'name1'],
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value2']],
                        ['identifier' => 'value2', 'name' => 'name2'],
                    ],
                    'refresh' => 'wait_for',
                ])->willReturn([
                    'took' => 1,
                    'errors' => false,
                    'items' => [
                        ['item_value1'],
                        ['item_value2'],
                    ],
                ]);
        $this->client->expects($this->exactly(1))->method('bulk')->with([
                    'body' => [
                        ['index' => ['_index' => 'an_index_name', '_id' => 'value3']],
                        ['identifier' => 'value3', 'name' => 'name3'],
                    ],
                    'refresh' => 'wait_for',
                ])->willReturn([
                    'took' => 1,
                    'errors' => false,
                    'items' => [
                        ['item_value3'],
                    ],
                ]);
        $documents = [
                    ['identifier' => 'value1', 'name' => 'name1'],
                    ['identifier' => 'value2', 'name' => 'name2'],
                    ['identifier' => 'value3', 'name' => 'name3'],
                ];
        $this->assertSame([
                    'took' => 2,
                    'errors' => false,
                    'items' => [
                        ['item_value1'],
                        ['item_value2'],
                        ['item_value3'],
                    ],
                ], $this->sut->bulkIndexes($documents, 'identifier', Refresh::waitFor()));
    }

    public function test_it_throws_an_exception_during_the_indexation_of_several_documents(): void
    {
        $this->client->method('bulk')->with($this->anything())->willThrowException(\Exception::class);
        $documents = [
                    ['identifier' => 'foo', 'name' => 'a name'],
                    ['identifier' => 'bar', 'name' => 'a name'],
                ];
        $this->expectException(IndexationException::class);
        $this->sut->bulkIndexes($documents, 'identifier', Refresh::waitFor());
    }

    public function test_it_triggers_an_exception_if_the_indexation_of_one_document_among_several_has_failed(): void
    {
        $this->client->method('bulk')->with($this->anything())->willReturn([
                        'errors' => true,
                        'items' => [
                            ['index' => []],
                            ['index' => ['error' => 'foo']]],
                    ]);
        $documents = [
                    ['identifier' => 'foo', 'name' => 'a name'],
                    ['identifier' => 'bar', 'name' => 'a name'],
                ];
        $this->expectException(IndexationException::class);
        $this->sut->bulkIndexes($documents, 'identifier', Refresh::waitFor());
    }

    public function test_it_throws_an_exception_if_identifier_key_is_missing(): void
    {
        $this->client->expects($this->never())->method('bulk')->with($this->anything());
        $documents = [
                    ['name' => 'a name'],
                    ['identifier' => 'bar', 'name' => 'a name'],
                ];
        $this->expectException(new MissingIdentifierException('Missing "identifier" key in document'));
        $this->sut->bulkIndexes($documents, 'identifier', Refresh::waitFor());
    }
}

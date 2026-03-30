<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\DualIndexationClient;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Namespaces\IndicesNamespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DualIndexationClientTest extends TestCase
{
    private Client|MockObject $nativeClient;
    private ClientBuilder|MockObject $clientBuilder;
    private Loader|MockObject $indexConfigurationLoader;
    private Client|MockObject $dualClient;
    private DualIndexationClient $sut;

    protected function setUp(): void
    {
        $this->nativeClient = $this->createMock(Client::class);
        $this->clientBuilder = $this->createMock(ClientBuilder::class);
        $this->indexConfigurationLoader = $this->createMock(Loader::class);
        $this->dualClient = $this->createMock(Client::class);
        $this->sut = new DualIndexationClient(
            $this->clientBuilder,
            $this->indexConfigurationLoader,
            ['localhost:9200'],
            'an_index_name',
            '',
            100_000_000,
            $this->dualClient
        );
        $this->clientBuilder->method('setHosts')->with(['localhost:9200'])->willReturn($this->clientBuilder);
        $this->clientBuilder->method('build')->willReturn($this->nativeClient);
    }

    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(DualIndexationClient::class, $this->sut);
        $this->assertInstanceOf(Client::class, $this->sut);
    }

    public function test_it_indexes_on_both_clients(): void
    {
        $this->nativeClient->method('index')->with([
                        'index' => 'an_index_name',
                        'id' => 'identifier',
                        'body' => ['a key' => 'a value'],
                        'refresh' => 'wait_for',
                    ])->willReturn(['errors' => false]);
        $this->dualClient->expects($this->once())->method('index')->with('identifier', ['a key' => 'a value'], Refresh::waitFor());
        $this->assertSame(['errors' => false], $this->sut->index('identifier', ['a key' => 'a value'], Refresh::waitFor()));
    }

    public function test_it_bulk_indexes_on_both_clients(): void
    {
        $expectedResponse = [
                    'took' => 1,
                    'errors' => false,
                    'items' => [
                        ['item_foo'],
                        ['item_bar'],
                    ],
                ];
        $this->nativeClient->expects($this->once())->method('bulk')->with([
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
        $this->dualClient->expects($this->once())->method('bulkIndexes')->with($documents, 'identifier', Refresh::waitFor());
        $this->assertSame($expectedResponse, $this->sut->bulkIndexes($documents, 'identifier', Refresh::waitFor()));
    }

    public function test_it_deletes_by_query_on_both_clients(): void
    {
        $query = ['foo' => 'bar'];
        $this->nativeClient->expects($this->once())->method('deleteByQuery')->with([
                    'index' => 'an_index_name',
                    'body' => $query,
                ]);
        $this->dualClient->expects($this->once())->method('deleteByQuery')->with($query);
        $this->sut->deleteByQuery($query);
    }

    public function test_it_refreshes_both_indexes(): void
    {
        $indices = $this->createMock(IndicesNamespace::class);

        $this->nativeClient->method('indices')->willReturn($indices);
        $indices->method('refresh')->with(['index' => 'an_index_name'])->willReturn(['errors' => false]);
        $this->dualClient->expects($this->once())->method('refreshIndex');
        $this->assertSame(['errors' => false], $this->sut->refreshIndex());
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Command\BackoffElasticSearchStateHandler;
use Akeneo\Pim\Enrichment\Bundle\Command\BulkEsHandlerInterface;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use PHPStan\Type\Php\ArgumentBasedFunctionReturnTypeExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class BackoffElasticSearchStateHandlerTest extends TestCase
{
    private BackoffElasticSearchStateHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new BackoffElasticSearchStateHandler(2, 2);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(BackoffElasticSearchStateHandler::class, $this->sut);
    }

    public function test_it_will_stop_after_a_403_response(): void
    {
        $bulkEsHandler = $this->createMock(BulkEsHandlerInterface::class);

        $codes = range(1, 17);
        $bulkEsHandler->method('bulkExecute')->with($this->anything())->willThrowException(new BadRequest400Exception("", Response::HTTP_FORBIDDEN));
        $this->expectException(BadRequest400Exception::class);
        $this->sut->bulkExecute($codes,$bulkEsHandler);
        $bulkEsHandler->method('bulkExecute')->with($this->anything());
    }

    public function test_it_will_make_several_attempts_reducing_batch_size(): void
    {
        $bulkEsHandler = $this->createMock(BulkEsHandlerInterface::class);

        $codes = range(1, 17);
        $badRequest400Exception = new BadRequest400Exception("", Response::HTTP_TOO_MANY_REQUESTS);
        $bulkEsHandler->method('bulkExecute')->with($this->anything())->willThrowException($badRequest400Exception);
        $this->expectException(BadRequest400Exception::class);
        $this->sut->bulkExecute($codes,$bulkEsHandler);
        $bulkEsHandler->method('bulkExecute')->with($this->anything());
        $bulkEsHandler->method('bulkExecute')->with($codes);
        $bulkEsHandler->method('bulkExecute')->with(range(1,8));
        $bulkEsHandler->method('bulkExecute')->with(range(1,4));
    }

    public function test_it_will_reset_decrease_batch_size_after_error_and_reset_after_success(): void
    {
        $bulkEsHandler = $this->createMock(BulkEsHandlerInterface::class);

        $codes = range(1, 17);
        $badRequest400Exception = new BadRequest400Exception("", Response::HTTP_TOO_MANY_REQUESTS);
        $bulkEsHandler->method('bulkExecute')->with($codes)->willThrowException($badRequest400Exception);
        $bulkEsHandler->method('bulkExecute')->with(range(1,8))->willReturn(8);
        $bulkEsHandler->method('bulkExecute')->with(range(9,16))->willReturn(8);
        $bulkEsHandler->method('bulkExecute')->with([17])->willReturn(1);
        $this->assertSame(17, $this->sut->bulkExecute($codes,$bulkEsHandler));
        $bulkEsHandler->method('bulkExecute')->with($this->anything());
        $bulkEsHandler->method('bulkExecute')->with($codes);
        $bulkEsHandler->method('bulkExecute')->with(range(1,8));
        $bulkEsHandler->method('bulkExecute')->with(range(9,16));
        $bulkEsHandler->method('bulkExecute')->with([17]);
    }
}

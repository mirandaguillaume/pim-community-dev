<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Exception;

use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class DocumentedHttpExceptionTest extends TestCase
{
    private DocumentedHttpException $sut;

    protected function setUp(): void
    {
    }

    public function test_it_creates_an_object_updater_http_exception(): void
    {
        $previous = new \Exception();
        $this->sut = new DocumentedHttpException('http://example.com', 'Property "xx" does not exist', $previous, 0);
        $this->assertTrue(is_a(DocumentedHttpException::class, DocumentedHttpException::class, true));
        $this->assertSame('http://example.com', $this->sut->getHref());
        $this->assertSame('Property "xx" does not exist', $this->sut->getMessage());
        $this->assertSame(0, $this->sut->getCode());
        $this->assertSame($previous, $this->sut->getPrevious());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->sut->getStatusCode());
    }
}

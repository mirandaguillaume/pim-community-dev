<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Api\Exception;

use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ViolationHttpExceptionTest extends TestCase
{
    private ViolationHttpException $sut;

    protected function setUp(): void
    {
    }

    public function test_it_creates_an_object_updater_http_exception(): void
    {
        $constraintViolation = $this->createMock(ConstraintViolationListInterface::class);

        $previous = new \Exception();
        $this->sut = new ViolationHttpException($constraintViolation, 'Property "xx" does not exist', $previous, 0);
        $this->assertTrue(is_a(ViolationHttpException::class, ViolationHttpException::class, true));
        $this->assertSame($constraintViolation, $this->sut->getViolations());
        $this->assertSame('Property "xx" does not exist', $this->sut->getMessage());
        $this->assertSame(0, $this->sut->getCode());
        $this->assertSame($previous, $this->sut->getPrevious());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->sut->getStatusCode());
    }
}

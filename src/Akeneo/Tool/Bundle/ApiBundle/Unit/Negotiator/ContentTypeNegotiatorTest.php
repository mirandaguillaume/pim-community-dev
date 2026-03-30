<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ApiBundle\Negotiator;

use Akeneo\Tool\Bundle\ApiBundle\Negotiator\ContentTypeNegotiator;
use FOS\RestBundle\Util\StopFormatListenerException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class ContentTypeNegotiatorTest extends TestCase
{
    private RequestMatcherInterface|MockObject $requestMatcher1;
    private RequestMatcherInterface|MockObject $requestMatcher2;
    private RequestMatcherInterface|MockObject $requestMatcher3;
    private ContentTypeNegotiator $sut;

    protected function setUp(): void
    {
        $this->requestMatcher1 = $this->createMock(RequestMatcherInterface::class);
        $this->requestMatcher2 = $this->createMock(RequestMatcherInterface::class);
        $this->requestMatcher3 = $this->createMock(RequestMatcherInterface::class);
        $this->sut = new ContentTypeNegotiator();
        $this->sut->add($this->requestMatcher2, ['content_types' => ['application/json'], 'priority' => 10]);
        $this->sut->add($this->requestMatcher1, ['content_types' => ['application/json'], 'priority' => 1]);
        $this->sut->add($this->requestMatcher3, ['stop' => true, 'priority' => 100]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ContentTypeNegotiator::class, $this->sut);
    }

    public function test_it_returns_content_types_for_a_matching_request_by_order_of_priority(): void
    {
        $request = $this->createMock(Request::class);

        $this->requestMatcher1->expects($this->once())->method('matches')->with($request)->willReturn(false);
        $this->requestMatcher2->expects($this->once())->method('matches')->with($request)->willReturn(true);
        $this->assertSame(['application/json'], $this->sut->getContentTypes($request));
    }

    public function test_it_throws_stop_format_exception_when_matching_request_with_stop_rule(): void
    {
        $request = $this->createMock(Request::class);

        $this->requestMatcher1->method('matches')->with($request)->willReturn(false);
        $this->requestMatcher2->method('matches')->with($request)->willReturn(false);
        $this->requestMatcher3->method('matches')->with($request)->willReturn(true);
        $this->expectException(StopFormatListenerException::class);

        $this->expectExceptionMessage('Stopped content type negotiator');
        $this->sut->getContentTypes($request);
    }
}

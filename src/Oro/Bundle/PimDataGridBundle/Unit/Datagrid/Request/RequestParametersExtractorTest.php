<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Datagrid\Request;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestParametersExtractorTest extends TestCase
{
    private RequestParameters|MockObject $requestParams;
    private RequestStack|MockObject $requestStack;
    private RequestParametersExtractor $sut;

    protected function setUp(): void
    {
        $this->requestParams = $this->createMock(RequestParameters::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->sut = new RequestParametersExtractor($this->requestParams, $this->requestStack);
    }

    public function test_it_extracts_the_parameter_from_the_datagrid_request(): void
    {
        $request = $this->createMock(Request::class);

        $this->requestStack->method('getCurrentRequest')->willReturn($request);
        $this->requestParams->expects($this->once())->method('get')->with('dataLocale', null)->willReturn('en_US');
        $result = $this->sut->getParameter('dataLocale');
        $this->assertSame('en_US', $result);
    }

    public function test_it_extracts_the_parameter_from_the_symfony_request(): void
    {
        $request = $this->createMock(Request::class);

        $this->requestStack->method('getCurrentRequest')->willReturn($request);
        $this->requestParams->expects($this->once())->method('get')->with('dataLocale', null);
        $request->expects($this->once())->method('get')->with('dataLocale', null)->willReturn('en_US');
        $result = $this->sut->getParameter('dataLocale');
        $this->assertSame('en_US', $result);
    }

    public function test_it_trows_a_logic_exception_when_the_parameter_is_not_present(): void
    {
        $request = $this->createMock(Request::class);

        $this->requestStack->method('getCurrentRequest')->willReturn($request);
        $this->requestParams->expects($this->once())->method('get')->with('dataLocale', null)->willReturn(null);
        $request->expects($this->once())->method('get')->with('dataLocale', null)->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Parameter "dataLocale" is expected');
        $this->sut->getParameter('dataLocale');
    }
}

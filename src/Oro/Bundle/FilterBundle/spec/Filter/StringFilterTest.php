<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\FilterBundle\Filter;

use Doctrine\ORM\Query\Expr;
use Oro\Bundle\FilterBundle\Datasource\ExpressionBuilderInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Filter\StringFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class StringFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private FilterUtility|MockObject $util;
    private StringFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->util = $this->createMock(FilterUtility::class);
        $this->sut = new StringFilter($this->factory, $this->util);
        $this->sut->init('teststring', ['data_name' => 'some_data_name']);
    }

    public function test_it_is_a_filter(): void
    {
        $this->assertInstanceOf(FilterInterface::class, $this->sut);
    }

    public function test_it_is_a_string_filter(): void
    {
        $this->assertInstanceOf(StringFilter::class, $this->sut);
    }

    public function test_it_applies_empty_filter(): void
    {
        $ds = $this->createMock(FilterDatasourceAdapterInterface::class);
        $builder = $this->createMock(ExpressionBuilderInterface::class);

        $ds->method('generateParameterName')->with('teststring')->willReturn('teststring1234');
        $isNullExpr = 'teststring IS NULL';
        $eqExpr = 'teststring = :testrting1234';
        $orExpr = new Expr\Orx();
        $builder->method('isNull')->with($this->isType('string'))->willReturn($isNullExpr);
        $builder->method('eq')->with($this->isType('string'), $this->isType('string'), true)->willReturn($eqExpr);
        $builder->method('orX')->with($isNullExpr, $eqExpr)->willReturn($orExpr);
        $ds->method('expr')->willReturn($builder);
        $ds->expects($this->once())->method('addRestriction')->with($orExpr, 'AND', false);
        $ds->expects($this->once())->method('setParameter')->with('teststring1234', '');
        $this->assertSame(true, $this->sut->apply($ds, ['type' => 'empty', 'value' => '']));
    }

    public function test_it_escapes_special_characters_with_like_operator(): void
    {
        $ds = $this->createMock(FilterDatasourceAdapterInterface::class);
        $builder = $this->createMock(ExpressionBuilderInterface::class);

        $this->sut->init('code', ['data_name' => 'a.code']);
        $ds->method('generateParameterName')->with('code')->willReturn('code1877008211');
        $comparisonExpr = new Expr\Comparison('a.code', 'LIKE', ':code1877008211');
        $builder->method('comparison')->with('a.code', 'LIKE', 'code1877008211', true)->willReturn($comparisonExpr);
        $ds->method('expr')->willReturn($builder);
        $ds->expects($this->once())->method('addRestriction')->with($comparisonExpr, 'AND', false);
        $ds->expects($this->once())->method('setParameter')->with('code1877008211', '%fabric\_%');
        $this->assertSame(true, $this->sut->apply($ds, ['type' => 1, 'value' => 'fabric_']));
    }

    public function test_it_does_not_escape_special_characters_with_equal_operator(): void
    {
        $ds = $this->createMock(FilterDatasourceAdapterInterface::class);
        $builder = $this->createMock(ExpressionBuilderInterface::class);

        $this->sut->init('code', ['data_name' => 'a.code']);
        $ds->method('generateParameterName')->with('code')->willReturn('code1877008211');
        $comparisonExpr = new Expr\Comparison('a.code', '=', ':code1877008211');
        $builder->method('comparison')->with('a.code', '=', 'code1877008211', true)->willReturn($comparisonExpr);
        $ds->method('expr')->willReturn($builder);
        $ds->expects($this->once())->method('addRestriction')->with($comparisonExpr, 'AND', false);
        $ds->expects($this->once())->method('setParameter')->with('code1877008211', 'fabric_');
        $this->assertSame(true, $this->sut->apply($ds, ['type' => 3, 'value' => 'fabric_']));
    }
}

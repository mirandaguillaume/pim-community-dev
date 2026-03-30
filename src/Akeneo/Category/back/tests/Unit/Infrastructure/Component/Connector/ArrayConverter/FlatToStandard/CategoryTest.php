<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Category\Infrastructure\Component\Connector\ArrayConverter\FlatToStandard\Category;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private FieldsRequirementChecker|MockObject $fieldChecker;
    private Category $sut;

    protected function setUp(): void
    {
        $this->fieldChecker = $this->createMock(FieldsRequirementChecker::class);
        $this->sut = new Category($this->fieldChecker);
    }

    public function testItConverts(): void
    {
        $fields = [
            'code' => 'mycode',
            'parent' => 'master',
            'label-fr_FR' => 'Ma superbe catégorie',
            'label-en_US' => 'My awesome category',
        ];
        $this->assertSame([
            'labels' => [
                'fr_FR' => 'Ma superbe catégorie',
                'en_US' => 'My awesome category',
            ],
            'code' => 'mycode',
            'parent' => 'master',
        ], $this->sut->convert($fields));
    }

    public function testItThrowsAnExceptionIfRequiredFieldsAreNotInArray(): void
    {
        $item = ['not_a_code' => ''];
        $this->fieldChecker->method('checkFieldsPresence')->with($item, ['code'])->willThrowException(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'));
        $this->expectException(\LogicException::class);
        $this->sut->convert($item);
    }

    public function testItThrowsAnExceptionIfRequiredFieldCodeIsEmpty(): void
    {
        $item = ['parent' => 'master', 'code' => ''];
        $this->fieldChecker->method('checkFieldsPresence')->with($item, ['code'])->willThrowException(new \LogicException('Field "code" must be filled'));
        $this->expectException(\LogicException::class);
        $this->sut->convert($item);
    }

    public function testItThrowsAnExceptionIfRequiredFieldsAreEmpty(): void
    {
        $item = ['code' => ''];
        $this->fieldChecker->method('checkFieldsPresence')->with($item, ['code'])->willThrowException(new \LogicException('Field "code" must be filled'));
        $this->expectException(\LogicException::class);
        $this->sut->convert($item);
    }

    public function testCheckFieldsFillingIsCalled(): void
    {
        $item = ['code' => 'mycode'];
        $this->fieldChecker->expects($this->once())->method('checkFieldsPresence')->with($item, ['code']);
        $this->fieldChecker->expects($this->once())->method('checkFieldsFilling')->with($item, ['code']);
        $this->sut->convert($item);
    }

    public function testEmptyCodeFieldIsExcludedFromResult(): void
    {
        $item = ['code' => '', 'label-en_US' => 'Hello'];
        $result = $this->sut->convert($item);
        $this->assertArrayNotHasKey('code', $result);
        $this->assertSame(['en_US' => 'Hello'], $result['labels']);
    }

    public function testEmptyParentFieldIsExcludedFromResult(): void
    {
        $item = ['code' => 'mycode', 'parent' => ''];
        $result = $this->sut->convert($item);
        $this->assertArrayNotHasKey('parent', $result);
        $this->assertSame('mycode', $result['code']);
    }

    public function testCodeFieldIsCastToString(): void
    {
        $item = ['code' => 123];
        $result = $this->sut->convert($item);
        $this->assertSame('123', $result['code']);
    }
}

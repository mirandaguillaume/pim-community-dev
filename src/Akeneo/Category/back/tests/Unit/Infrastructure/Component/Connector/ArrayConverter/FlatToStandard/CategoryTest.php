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

    public function test_it_converts(): void
    {
        $fields = [
            'code'        => 'mycode',
            'parent'      => 'master',
            'label-fr_FR' => 'Ma superbe catégorie',
            'label-en_US' => 'My awesome category',
        ];
        $this->assertSame([
            'labels'   => [
                'fr_FR' => 'Ma superbe catégorie',
                'en_US' => 'My awesome category',
            ],
            'code'     => 'mycode',
            'parent'   => 'master',
        ], $this->sut->convert($fields));
    }

    public function test_it_throws_an_exception_if_required_fields_are_not_in_array(): void
    {
        $item = ['not_a_code' => ''];
        $this->fieldChecker->method('checkFieldsPresence')->with($item, ['code'])->willThrowException(new \LogicException('Field "code" is expected, provided fields are "not_a_code"'));
        $this->expectException(\LogicException::class);
        $this->sut->convert($item);
    }

    public function test_it_throws_an_exception_if_required_field_code_is_empty(): void
    {
        $item = ['parent' => 'master', 'code' => ''];
        $this->fieldChecker->method('checkFieldsPresence')->with($item, ['code'])->willThrowException(new \LogicException('Field "code" must be filled'));
        $this->expectException(\LogicException::class);
        $this->sut->convert($item);
    }

    public function test_it_throws_an_exception_if_required_fields_are_empty(): void
    {
        $item = ['code' => ''];
        $this->fieldChecker->method('checkFieldsPresence')->with($item, ['code'])->willThrowException(new \LogicException('Field "code" must be filled'));
        $this->expectException(\LogicException::class);
        $this->sut->convert($item);
    }
}

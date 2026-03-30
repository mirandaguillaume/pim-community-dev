<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\ArrayConverter;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\ArrayConverter\DummyConverter;

class DummyConverterTest extends TestCase
{
    private FieldsRequirementChecker|MockObject $checker;
    private DummyConverter $sut;

    protected function setUp(): void
    {
        $this->checker = $this->createMock(FieldsRequirementChecker::class);
        $this->sut = new DummyConverter($this->checker, $fieldsPresence, $fieldsFilling);
        $fieldsPresence = ['uuid', 'name', 'code'];
        $fieldsFilling = ['uuid', 'name'];
    }

    public function test_it_checks_fields_when_converting(): void
    {
        $item = [
                    'uuid'     => 'effeacef4848484',
                    'name'     => 'Long sword',
                    'code'     => 'long_sword',
                    'material' => '',
                ];
        $this->checker->expects($this->once())->method('checkFieldsPresence')->with($item, ['uuid', 'name', 'code']);
        $this->checker->expects($this->once())->method('checkFieldsFilling')->with($item, ['uuid', 'name']);
        $this->sut->convert($item);
    }

    public function test_it_converts_to_the_same_array_format(): void
    {
        $item = [
                    'uuid'     => 'effeacef4848484',
                    'name'     => 'Long sword',
                    'code'     => 'long_sword',
                    'material' => '',
                ];
        $this->assertSame($item, $this->sut->convert($item));
    }
}

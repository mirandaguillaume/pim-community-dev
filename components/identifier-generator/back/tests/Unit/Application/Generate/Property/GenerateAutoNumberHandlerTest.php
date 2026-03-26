<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateAutoNumberHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\GetNextIdentifierQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateAutoNumberHandlerTest extends TestCase
{
    private GetNextIdentifierQuery|MockObject $getNextIdentifierQuery;
    private GenerateAutoNumberHandler $sut;

    protected function setUp(): void
    {
        $this->getNextIdentifierQuery = $this->createMock(GetNextIdentifierQuery::class);
        $this->sut = new GenerateAutoNumberHandler($this->getNextIdentifierQuery);
    }

    public function test_it_should_support_only_auto_numbers(): void
    {
        $this->assertSame(AutoNumber::class, $this->sut->getPropertyClass());
    }

    public function test_it_should_throw_exception_when_invoked_with_something_else_than_auto_number(): void
    {
        $freeText = FreeText::fromNormalized([
            'type' => FreeText::type(),
            'string' => 'AKN-',
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($freeText);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke(
            $freeText,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        );
    }

    public function test_it_should_return_next_number(): void
    {
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 1,
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);
        $this->getNextIdentifierQuery->expects($this->once())->method('fromPrefix')->with($identifierGenerator, 'AKN-', 0)->willReturn(42);
        $this->assertSame('42', $this->sut->__invoke(
            $autoNumber,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        ));
    }

    public function test_it_should_set_min_number(): void
    {
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 50,
            'digitsMin' => 1,
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);
        $this->getNextIdentifierQuery->expects($this->once())->method('fromPrefix')->with($identifierGenerator, 'AKN-', 50)->willReturn(50);
        $this->assertSame('50', $this->sut->__invoke(
            $autoNumber,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        ));
    }

    public function test_it_should_add_digits_when_number_is_too_low(): void
    {
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 5,
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);
        $this->getNextIdentifierQuery->expects($this->once())->method('fromPrefix')->with($identifierGenerator, 'AKN-', 0)->willReturn(42);
        $this->assertSame('00042', $this->sut->__invoke(
            $autoNumber,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        ));
    }

    public function test_it_should_not_add_digits_when_number_is_too_high(): void
    {
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 5,
        ]);
        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);
        $this->getNextIdentifierQuery->expects($this->once())->method('fromPrefix')->with($identifierGenerator, 'AKN-', 0)->willReturn(426942);
        $this->assertSame('426942', $this->sut->__invoke(
            $autoNumber,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        ));
    }

    private function getIdentifierGenerator(PropertyInterface $property): IdentifierGenerator
    {
        return new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('my_generator'),
            Conditions::fromArray([]),
            Structure::fromArray([$property]),
            LabelCollection::fromNormalized(['en_US' => 'MyGenerator']),
            Target::fromString('sku'),
            Delimiter::fromString(null),
            TextTransformation::fromString('no'),
        );
    }
}

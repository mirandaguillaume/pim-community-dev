<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Generate;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\GenerateIdentifierCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\GenerateIdentifierHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateAutoNumberHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateFreeTextHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\GetNextIdentifierQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateIdentifierHandlerTest extends TestCase
{
    private GetNextIdentifierQuery|MockObject $getNextIdentifierQuery;
    private GenerateIdentifierHandler $sut;

    protected function setUp(): void
    {
        $this->getNextIdentifierQuery = $this->createMock(GetNextIdentifierQuery::class);
        $this->sut = new GenerateIdentifierHandler(new \ArrayIterator([
            new GenerateAutoNumberHandler($this->getNextIdentifierQuery),
            new GenerateFreeTextHandler(),
        ]));
    }

    public function test_it_should_generate_an_identifier_without_delimiter(): void
    {
        $identifierGenerator = $this->getIdentifierGenerator(Delimiter::fromString(null));
        $generateIdentifierCommand = GenerateIdentifierCommand::fromIdentifierGenerator(
                    $identifierGenerator,
                    new ProductProjection(true, null, [], []),
                );
        $this->getNextIdentifierQuery->expects($this->once())->method('fromPrefix')->with($identifierGenerator, 'aKn-', 0)->willReturn(43);
        $this->assertSame('aKn-43', $this->sut->__invoke($generateIdentifierCommand));
    }

    public function test_it_should_generate_an_identifier_with_delimiter(): void
    {
        $identifierGenerator = $this->getIdentifierGenerator(Delimiter::fromString('a'));
        $generateIdentifierCommand = GenerateIdentifierCommand::fromIdentifierGenerator(
                    $identifierGenerator,
                    new ProductProjection(true, null, [], []),
                );
        $this->getNextIdentifierQuery->expects($this->once())->method('fromPrefix')->with($identifierGenerator, 'aKn-a', 0)->willReturn(43);
        $this->assertSame('aKn-a43', $this->sut->__invoke($generateIdentifierCommand));
    }

    public function test_it_should_generate_an_identifier_with_delimiter_and_uppercase(): void
    {
        $identifierGenerator = $this->getIdentifierGenerator(Delimiter::fromString('x'), 'uppercase');
        $generateIdentifierCommand = GenerateIdentifierCommand::fromIdentifierGenerator(
                    $identifierGenerator,
                    new ProductProjection(true, null, [], []),
                );
        $this->getNextIdentifierQuery->expects($this->once())->method('fromPrefix')->with($identifierGenerator, 'AKN-X', 0)->willReturn(43);
        $this->assertSame('AKN-X43', $this->sut->__invoke($generateIdentifierCommand));
    }

    private function getIdentifierGenerator(
        Delimiter $delimiter,
        string $textTransformation = 'no',
    ): IdentifierGenerator
    {
            return new IdentifierGenerator(
                IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
                IdentifierGeneratorCode::fromString('my_generator'),
                Conditions::fromArray([]),
                Structure::fromArray([
                    FreeText::fromString('aKn-'),
                    AutoNumber::fromNormalized([
                        'type' => AutoNumber::type(),
                        'numberMin' => 0,
                        'digitsMin' => 1,
                    ]),
                ]),
                LabelCollection::fromNormalized(['en_US' => 'MyGenerator']),
                Target::fromString('sku'),
                $delimiter,
                TextTransformation::fromString($textTransformation),
            );
            $generateIdentifierCommand = GenerateIdentifierCommand::fromIdentifierGenerator(
                $identifierGenerator,
                new ProductProjection(true, null, [], [])
            );
    
            $getNextIdentifierQuery
                ->fromPrefix($identifierGenerator, 'AKN-', 0)
                ->shouldBeCalled()
                ->willReturn(43);
    
            $this->__invoke($generateIdentifierCommand)->shouldReturn('AKN-43');
        }
}

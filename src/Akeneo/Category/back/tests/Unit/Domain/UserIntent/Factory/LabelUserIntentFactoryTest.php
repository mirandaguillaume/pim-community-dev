<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Domain\UserIntent\Factory\LabelUserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\TestCase;

class LabelUserIntentFactoryTest extends TestCase
{
    private LabelUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new LabelUserIntentFactory();
    }

    public function testItManageOnlyExpectedFieldNames(): void
    {
        $this->assertSame(['labels'], $this->sut->getSupportedFieldNames());
    }

    public function testItCreatesAListOfLabelUserIntentsBasedOnLabelsList(): void
    {
        $this->assertEquals([
            new SetLabel('en_US', 'sausages'),
            new SetLabel('fr_FR', 'saucisses'),
        ], $this->sut->create(
            'labels',
            1,
            [
                'en_US' => 'sausages',
                'fr_FR' => 'saucisses',
            ],
        ));
    }

    public function testItDoesCreateLabelUserIntentWithNull(): void
    {
        $this->assertEquals([
            new SetLabel('en_US', null),
        ], $this->sut->create(
            'labels',
            1,
            ['en_US' => null],
        ));
    }

    public function testItThrowsAnExceptionWhenDataHasWrongFormat(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('labels', 1, null);
    }
}

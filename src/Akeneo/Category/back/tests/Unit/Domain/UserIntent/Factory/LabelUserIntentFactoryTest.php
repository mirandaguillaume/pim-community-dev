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

    public function test_it_manage_only_expected_field_names(): void
    {
        $this->assertSame(['labels'], $this->sut->getSupportedFieldNames());
    }

    public function test_it_creates_a_list_of_label_user_intents_based_on_labels_list(): void
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
            ]
        ));
    }

    public function test_it_does_create_label_user_intent_with_null(): void
    {
        $this->assertEquals([
            new SetLabel('en_US', null),
        ], $this->sut->create(
            'labels',
            1,
            ['en_US' => null]
        ));
    }

    public function test_it_throws_an_exception_when_data_has_wrong_format(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->create('labels', 1, null);
    }
}

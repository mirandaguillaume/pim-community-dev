<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Applier\SetLabelApplier;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class SetLabelApplierTest extends TestCase
{
    private SetLabelApplier $sut;

    protected function setUp(): void
    {
        $this->sut = new SetLabelApplier();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetLabelApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_applies_set_label_user_intent(): void
    {
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('my_category'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: null
        );
        $setLabelEN = new SetLabel('en_US', 'The label');
        $setLabelFR = new SetLabel('fr_FR', 'Le label');
        $setLabelNewEN = new SetLabel('en_US', 'The new label');
        $this->sut->apply($setLabelEN, $category);
        Assert::assertEquals('The label', $category->getLabels()->getTranslation('en_US'));
        $this->sut->apply($setLabelFR, $category);
        Assert::assertEquals('The label', $category->getLabels()->getTranslation('en_US'));
        $this->sut->apply($setLabelNewEN, $category);
        Assert::assertEquals('The new label', $category->getLabels()->getTranslation('en_US'));
    }
}

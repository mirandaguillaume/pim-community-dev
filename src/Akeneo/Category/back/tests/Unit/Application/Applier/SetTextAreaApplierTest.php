<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Application\Applier\SetTextAreaApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextAreaValue;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SetTextAreaApplierTest extends TestCase
{
    private SetTextAreaApplier $sut;

    protected function setUp(): void
    {
        $this->sut = new SetTextAreaApplier();
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(SetTextAreaApplier::class, $this->sut);
    }

    public function testItUpdatesCategoryValueCollection(): void
    {
        $givenTextAreaValue = TextAreaValue::fromApplier(
            value: 'Meta'.PHP_EOL.' shoes',
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US',
        );
        $attributes = ValueCollection::fromArray([$givenTextAreaValue]);
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            attributes: $attributes,
        );
        $userIntent = new SetTextArea(
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
            'seo_meta_description',
            'ecommerce',
            'en_US',
            'New Meta'.PHP_EOL.' shoes',
        );
        $expectedAttributes = ValueCollection::fromArray([
            TextAreaValue::fromApplier(
                value: 'New Meta'.PHP_EOL.' shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US',
            ),
        ]);
        $this->sut->apply($userIntent, $category);
        Assert::assertEquals(
            $expectedAttributes,
            $category->getAttributes(),
        );
    }

    public function testItAppliesSetTextAreaUserIntentWhenAttributesAreNull(): void
    {
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            attributes: null,
        );
        $userIntent = new SetTextArea(
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
            'seo_meta_description',
            'ecommerce',
            'en_US',
            'New Meta'.PHP_EOL.' shoes',
        );
        $expectedAttributes = ValueCollection::fromArray([
            TextAreaValue::fromApplier(
                value: 'New Meta'.PHP_EOL.' shoes',
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US',
            ),
        ]);
        $this->sut->apply($userIntent, $category);
        $this->assertNotNull($category->getAttributes());
        Assert::assertEquals($expectedAttributes, $category->getAttributes());
    }

    public function testItThrowsExceptionOnWrongUserIntentApplied(): void
    {
        $userIntent = $this->createMock(SetText::class);
        $category = $this->createMock(Category::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($userIntent, $category);
    }
}

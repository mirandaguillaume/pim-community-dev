<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\SetRichText;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Application\Applier\SetRichTextApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextAreaValue;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SetRichTextApplierTest extends TestCase
{
    private SetRichTextApplier $sut;

    protected function setUp(): void
    {
        $this->sut = new SetRichTextApplier();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetRichTextApplier::class, $this->sut);
    }

    public function test_it_updates_category_value_collection(): void
    {
        $givenRichTextValue = TextAreaValue::fromApplier(
            value: "<p>Meta shoes</p>",
            uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
            code: 'seo_meta_description',
            channel: 'ecommerce',
            locale: 'en_US'
        );
        $attributes = ValueCollection::fromArray([$givenRichTextValue]);
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('code'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            attributes: $attributes
        );
        $userIntent = new SetRichText(
            '69e251b3-b876-48b5-9c09-92f54bfb528d',
            'seo_meta_description',
            'ecommerce',
            'en_US',
            "<p>New Meta shoes</p>"
        );
        $expectedAttributes = ValueCollection::fromArray([
            TextAreaValue::fromApplier(
                value: "<p>New Meta shoes</p>",
                uuid: '69e251b3-b876-48b5-9c09-92f54bfb528d',
                code: 'seo_meta_description',
                channel: 'ecommerce',
                locale: 'en_US'
            ),
        ]);
        $this->sut->apply($userIntent, $category);
        Assert::assertEquals(
            $expectedAttributes,
            $category->getAttributes()
        );
    }

    public function test_it_throws_exception_on_wrong_user_intent_applied(): void
    {
        $userIntent = $this->createMock(SetText::class);
        $category = $this->createMock(Category::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->apply($userIntent, $category);
    }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Enrichment\Filter;

use Akeneo\Category\Application\Enrichment\Filter\ByChannelAndLocalesFilter;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ByChannelAndLocalesFilterTest extends TestCase
{
    private ByChannelAndLocalesFilter $sut;

    protected function setUp(): void
    {
        $this->sut = new ByChannelAndLocalesFilter();
    }

    public function testItReturnsTheListOfEnrichedValuesToCleanWhileCleaningChannel(): void
    {
        $valuesToFilter = ValueCollection::fromDatabase($this->getEnrichedValues());
        $this->assertSame([
            $valuesToFilter->getValue(
                'long_description',
                'c91e6a4e-733b-4d77-aefc-129edbf03233',
                'mobile',
                'fr_FR',
            ),
            $valuesToFilter->getValue(
                'long_description',
                'c91e6a4e-733b-4d77-aefc-129edbf03233',
                'mobile',
                'en_US',
            ),
        ], $this->sut->getEnrichedValuesToClean($valuesToFilter, 'mobile', []));
    }

    public function testItDoesNothingWhenDeletedChannelCodeIsNull(): void
    {
        $valuesToFilter = ValueCollection::fromDatabase($this->getEnrichedValues());
        $this->assertSame([], $this->sut->getEnrichedValuesToClean($valuesToFilter, '', []));
    }

    public function testItReturnsTheListOfEnrichedValuesToCleanWhileCleaningLocales(): void
    {
        $valuesToFilter = ValueCollection::fromDatabase($this->getEnrichedValues());
        $this->assertSame([
            $valuesToFilter->getValue(
                'long_description',
                'c91e6a4e-733b-4d77-aefc-129edbf03233',
                'mobile',
                'fr_FR',
            ),
        ], $this->sut->getEnrichedValuesToClean($valuesToFilter, 'mobile', ['en_US']));
    }

    public function testItReturnsAnEmptyListOfEnrichedValuesToCleanWhenNoValuesHasToBeCleaned(): void
    {
        $valuesToFilter = ValueCollection::fromDatabase($this->getEnrichedValues());
        $this->assertSame([], $this->sut->getEnrichedValuesToClean($valuesToFilter, 'unknown_channel', []));
        $this->assertSame([], $this->sut->getEnrichedValuesToClean($valuesToFilter, 'mobile', ['en_US', 'fr_FR']));
    }

    private function getEnrichedValues(): array
    {
        return json_decode(
            '{
                    "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|ecommerce|fr_FR": {
                        "data": "<p>Ma description enrichie pour le ecommerce</p>\n",
                        "type": "textarea",
                        "locale": "fr_FR",
                        "channel": "ecommerce",
                        "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
                    },
                    "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|fr_FR": {
                        "data": "<p>Ma description enrichie pour le mobile</p>\n",
                        "type": "textarea",
                        "locale": "fr_FR",
                        "channel": "mobile",
                        "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
                    },
                    "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233|mobile|en_US": {
                        "data": "<p>My enriched description for mobile</p>\n",
                        "type": "textarea",
                        "locale": "en_US",
                        "channel": "mobile",
                        "attribute_code": "long_description|c91e6a4e-733b-4d77-aefc-129edbf03233"
                    },
                    "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911|en_US": {
                        "data": "my_url_slug",
                        "type": "text",
                        "locale": "en_US",
                        "channel": null,
                        "attribute_code": "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911"
                    },
                    "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911": {
                        "data": "all_scope_all_locale_url_slug",
                        "type": "text",
                        "locale": null,
                        "channel": null,
                        "attribute_code": "url_slug|d8617b1f-1db8-4e49-a6b0-404935fe2911"
                    }
                }',
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
    }
}

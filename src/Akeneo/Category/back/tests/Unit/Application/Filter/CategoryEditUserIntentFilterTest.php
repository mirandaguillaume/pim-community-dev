<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Filter;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\SetText;
use Akeneo\Category\Application\Filter\CategoryEditUserIntentFilter;
use PHPUnit\Framework\TestCase;

/**
 * CategoryEditUserIntentFilter is, as of today, an empty extension point: filterCollection() is a
 * literal pass-through. These tests therefore lock a contract, not a behaviour — they exist so that
 * adding any filtering here becomes a deliberate act that breaks a test instead of silently
 * dropping user intents on the category edition path.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryEditUserIntentFilterTest extends TestCase
{
    private CategoryEditUserIntentFilter $sut;

    protected function setUp(): void
    {
        $this->sut = new CategoryEditUserIntentFilter();
    }

    public function testItReturnsEveryUserIntentInTheSameOrderAndWithTheSameInstances(): void
    {
        $setEnLabel = new SetLabel('en_US', 'socks');
        $setFrLabel = new SetLabel('fr_FR', 'chaussettes');
        $setText = new SetText('a_uuid', 'a_code', null, 'en_US', 'a text value');

        $filtered = $this->sut->filterCollection([$setEnLabel, $setFrLabel, $setText]);

        $this->assertCount(3, $filtered);
        $this->assertSame($setEnLabel, $filtered[0]);
        $this->assertSame($setFrLabel, $filtered[1]);
        $this->assertSame($setText, $filtered[2]);
    }

    public function testItReturnsAnEmptyCollectionWhenNothingIsToBeFiltered(): void
    {
        $this->assertSame([], $this->sut->filterCollection([]));
    }

    public function testItPreservesTheKeysOfTheCollection(): void
    {
        // StandardFormatToUserIntents::convert() filters with array_filter() and can therefore hand
        // over a gapped array; the filter must not reindex it.
        $setLabel = new SetLabel('en_US', 'socks');

        $this->assertSame([1 => $setLabel], $this->sut->filterCollection([1 => $setLabel]));
    }
}

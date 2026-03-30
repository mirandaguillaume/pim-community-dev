<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field\CategoryFieldClearer;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFieldClearerTest extends TestCase
{
    private CategoryFieldClearer $sut;

    protected function setUp(): void
    {
        $this->sut = new CategoryFieldClearer();
    }

}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\Category\OnDelete;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Category\OnDelete\RemoveCategoryFilterInJobInstanceSubscriber;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveCategoryFilterInJobInstanceSubscriberTest extends TestCase
{
    private RemoveCategoryFilterInJobInstanceSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoveCategoryFilterInJobInstanceSubscriber();
    }

    private function createCategory(string $code, array $children = []): Category
    {
            $category = new Category();
            $category->setCode($code);
            foreach ($children as $child) {
                $category->addChild($child);
            }
    
            return $category;
        }
}

<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Storage\Sql\Category;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\GetDescendentCategoryCodes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetDescendentCategoryCodesTest extends TestCase
{
    private Connection|MockObject $connection;
    private GetDescendentCategoryCodes $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->sut = new GetDescendentCategoryCodes($this->connection);
    }

    public function test_it_returns_the_codes_of_every_descendent_of_the_given_category(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $category->method('getLeft')->willReturn(4);
        $category->method('getRight')->willReturn(11);
        $category->method('getRoot')->willReturn(1);

        $result = $this->createMock(Result::class);
        $result->method('fetchFirstColumn')->willReturn(['shoes', 'sneakers']);
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with(
                $this->anything(),
                [
                    'parent_category_left' => 4,
                    'parent_category_right' => 11,
                    'parent_category_root' => 1,
                ],
            )
            ->willReturn($result);

        $this->assertSame(['shoes', 'sneakers'], ($this->sut)($category));
    }
}

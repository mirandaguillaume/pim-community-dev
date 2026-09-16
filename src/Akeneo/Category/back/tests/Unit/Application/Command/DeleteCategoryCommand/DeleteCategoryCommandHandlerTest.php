<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Command\DeleteCategoryCommand;

use Akeneo\Category\Application\Command\DeleteCategoryCommand\DeleteCategoryCommand;
use Akeneo\Category\Application\Command\DeleteCategoryCommand\DeleteCategoryCommandHandler;
use Akeneo\Category\Domain\Query\DeleteCategoryTreeTemplateByTemplateUuid;
use Akeneo\Category\Domain\Query\GetCategoryTreeTemplates;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCategoryCommandHandlerTest extends TestCase
{
    private CategoryRepositoryInterface|MockObject $categoryRepository;
    private RemoverInterface|MockObject $remover;
    private GetCategoryTreeTemplates|MockObject $getCategoryTreeTemplates;
    private DeleteCategoryTreeTemplateByTemplateUuid|MockObject $deleteCategoryTreeTemplateByTemplateUuid;
    private DeleteCategoryCommandHandler $sut;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->remover = $this->createMock(RemoverInterface::class);
        $this->getCategoryTreeTemplates = $this->createMock(GetCategoryTreeTemplates::class);
        $this->deleteCategoryTreeTemplateByTemplateUuid = $this->createMock(DeleteCategoryTreeTemplateByTemplateUuid::class);
        $this->sut = new DeleteCategoryCommandHandler(
            $this->categoryRepository,
            $this->remover,
            $this->getCategoryTreeTemplates,
            $this->deleteCategoryTreeTemplateByTemplateUuid,
        );
    }

    public function test_it_does_nothing_when_the_category_does_not_exist(): void
    {
        $this->categoryRepository->method('find')->with(42)->willReturn(null);
        $this->remover->expects($this->never())->method('remove');

        ($this->sut)(new DeleteCategoryCommand(42));
    }

    public function test_it_just_removes_a_non_root_category(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $category->method('isRoot')->willReturn(false);
        $this->categoryRepository->method('find')->with(42)->willReturn($category);
        $this->getCategoryTreeTemplates->expects($this->never())->method('__invoke');
        $this->remover->expects($this->once())->method('remove')->with($category);

        ($this->sut)(new DeleteCategoryCommand(42));
    }

    public function test_it_deletes_every_tree_template_before_removing_a_root_category(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $category->method('isRoot')->willReturn(true);
        $this->categoryRepository->method('find')->with(42)->willReturn($category);
        $templateUuid1 = TemplateUuid::fromString('c8b3a6c1-0000-4000-8000-000000000001');
        $templateUuid2 = TemplateUuid::fromString('c8b3a6c1-0000-4000-8000-000000000002');
        $this->getCategoryTreeTemplates->method('__invoke')
            ->with(new CategoryId(42))
            ->willReturn([$templateUuid1, $templateUuid2]);
        $this->deleteCategoryTreeTemplateByTemplateUuid->expects($this->exactly(2))
            ->method('__invoke')
            ->with($this->logicalOr($templateUuid1, $templateUuid2));
        $this->remover->expects($this->once())->method('remove')->with($category);

        ($this->sut)(new DeleteCategoryCommand(42));
    }
}

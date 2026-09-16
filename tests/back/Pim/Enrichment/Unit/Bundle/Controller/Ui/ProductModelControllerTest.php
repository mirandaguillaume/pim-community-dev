<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Controller\Ui;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Controller\Ui\ProductModelController;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelControllerTest extends TestCase
{
    private ProductModelRepositoryInterface|MockObject $productModelRepository;
    private CategoryRepositoryInterface|MockObject $categoryRepository;
    private SecurityFacade|MockObject $securityFacade;
    private Environment|MockObject $twig;
    private ProductModelController $sut;

    protected function setUp(): void
    {
        $this->productModelRepository = $this->createMock(ProductModelRepositoryInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->securityFacade = $this->createMock(SecurityFacade::class);
        $this->sut = new ProductModelController(
            $this->productModelRepository,
            $this->categoryRepository,
            $this->securityFacade,
            'ACategoryClass',
            'an_acl',
            'a_template.html.twig',
        );

        $this->twig = $this->createMock(Environment::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('twig')->willReturn(true);
        $container->method('get')->with('twig')->willReturn($this->twig);
        $this->sut->setContainer($container);
    }

    public function test_it_throws_access_denied_when_the_permission_is_missing(): void
    {
        $this->securityFacade->method('isGranted')->with('an_acl')->willReturn(false);

        $this->expectException(AccessDeniedException::class);

        $this->sut->listCategoriesAction(Request::create('/'), 'a_product_model_id', 'a_category_id');
    }

    public function test_it_throws_not_found_when_the_product_model_does_not_exist(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->productModelRepository->method('find')->with('unknown_id')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->sut->listCategoriesAction(Request::create('/'), 'unknown_id', 'a_category_id');
    }

    public function test_it_throws_not_found_when_the_category_does_not_exist(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $this->productModelRepository->method('find')->willReturn($this->createMock(ProductModelInterface::class));
        $this->categoryRepository->method('find')->with('unknown_category')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->sut->listCategoriesAction(Request::create('/'), 'a_product_model_id', 'unknown_category');
    }

    public function test_it_renders_the_categories_selected_by_code_when_a_selection_is_given(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $productModel = $this->createMock(ProductModelInterface::class);
        $this->productModelRepository->method('find')->willReturn($productModel);
        $category = $this->createMock(CategoryInterface::class);
        $this->categoryRepository->method('find')->willReturn($category);
        $selectedCategories = new ArrayCollection([$category]);
        $this->categoryRepository->expects($this->once())
            ->method('getCategoriesByCodes')
            ->with(['a_category_code'])
            ->willReturn($selectedCategories);
        $productModel->expects($this->never())->method('getCategories');
        $this->categoryRepository->method('getFilledTree')
            ->with($category, $selectedCategories)
            ->willReturn(['a_tree']);
        $this->twig->method('render')
            ->with('a_template.html.twig', ['trees' => ['a_tree'], 'categories' => $selectedCategories])
            ->willReturn('<rendered/>');

        $response = $this->sut->listCategoriesAction(
            Request::create('/', 'GET', ['selected' => ['a_category_code']]),
            'a_product_model_id',
            'a_category_id',
        );

        $this->assertSame('<rendered/>', $response->getContent());
    }

    public function test_it_renders_the_product_models_own_categories_when_no_selection_is_given(): void
    {
        $this->securityFacade->method('isGranted')->willReturn(true);
        $productModel = $this->createMock(ProductModelInterface::class);
        $this->productModelRepository->method('find')->willReturn($productModel);
        $category = $this->createMock(CategoryInterface::class);
        $this->categoryRepository->method('find')->willReturn($category);
        $ownCategories = new ArrayCollection([$category]);
        $productModel->method('getCategories')->willReturn($ownCategories);
        $this->categoryRepository->expects($this->never())->method('getCategoriesByCodes');
        $this->categoryRepository->method('getFilledTree')->willReturn(['a_tree']);
        $this->twig->method('render')->willReturn('<rendered/>');

        $response = $this->sut->listCategoriesAction(Request::create('/'), 'a_product_model_id', 'a_category_id');

        $this->assertSame('<rendered/>', $response->getContent());
    }
}

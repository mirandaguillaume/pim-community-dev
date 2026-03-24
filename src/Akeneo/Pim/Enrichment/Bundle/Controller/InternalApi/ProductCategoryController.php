<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product category controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryController
{
    protected \Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface $productRepository;

    protected \Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface $productCategoryRepository;

    protected \Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface $objectFilter;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductCategoryRepositoryInterface $productCategoryRepository,
        ObjectFilterInterface $objectFilter
    ) {
        $this->productRepository         = $productRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->objectFilter              = $objectFilter;
    }

    /**
     * List categories and trees for a product
     *
     * @param string $uuid
     *
     * @AclAncestor("pim_enrich_product_categories_view")
     *
     * @return JsonResponse
     */
    public function listAction(string $uuid): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $result = [];
        $product = $this->findProductOr404($uuid);
        $trees = $this->productCategoryRepository->getItemCountByTree($product);

        $result['trees'] = $this->buildTrees($trees);
        $result['categories'] = $this->buildCategories($product);

        return new JsonResponse($result);
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param string $uuid the product uuid
     *
     * @return ProductInterface
     * @throws NotFoundHttpException
     */
    protected function findProductOr404(string $uuid)
    {
        $product = $this->productRepository->find($uuid);

        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with uuid %s could not be found.', (string) $uuid)
            );
        }

        return $product;
    }

    /**
     * @return array
     */
    protected function buildTrees(array $trees): array
    {
        $result = [];

        foreach ($trees as $tree) {
            $category = $tree['tree'];

            if (!$this->objectFilter->filterObject($category, 'pim.internal_api.product_category.view')) {
                $result[] = [
                    'id'         => $category->getId(),
                    'code'       => $category->getCode(),
                    'label'      => $category->getLabel(),
                    'associated' => $tree['itemCount'] > 0,
                ];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function buildCategories(ProductInterface $product): array
    {
        $result = [];

        foreach ($product->getCategories() as $category) {
            $result[] = [
                'id'     => $category->getId(),
                'code'   => $category->getCode(),
                'rootId' => $category->getRoot(),
            ];
        }

        return $result;
    }
}

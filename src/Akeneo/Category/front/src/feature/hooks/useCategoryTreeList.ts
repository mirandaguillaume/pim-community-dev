import {useEffect, useState} from 'react';
import {useFetch, useRoute} from '@akeneo-pim-community/shared';
import {CategoryTreeModel} from '../models';

const useCategoryTreeList = () => {
  const [trees, setTrees] = useState<CategoryTreeModel[]>([]);
  const url = useRoute('pim_enrich_categorytree_listtree', {
    _format: 'json',
    include_sub: '0',
    with_items_count: '0',
    context: 'manage',
  });

  const [treesData, loadTrees, loadingStatus, loadingError] = useFetch<CategoryTreeModel[]>(url);

  const [productsNumberByCategory, fetchProductsNumberByCategory] = useFetch<{
    [categoryId: number]: number;
  }>(useRoute('pim_enrich_categorytree_get_products_number'));

  useEffect(() => {
    setTrees(treesData || []);
    if (treesData) {
      (async () => {
        await fetchProductsNumberByCategory();
      })();
    }
  }, [treesData, fetchProductsNumberByCategory]);

  // A functional update: this effect writes the trees it reads, so depending on `trees` would
  // re-run it on its own output, forever.
  useEffect(() => {
    if (productsNumberByCategory) {
      setTrees(currentTrees =>
        currentTrees.map((tree: CategoryTreeModel) => {
          if (Object.prototype.hasOwnProperty.call(productsNumberByCategory, tree.id)) {
            return {
              ...tree,
              productsNumber: productsNumberByCategory[tree.id],
            };
          }

          return tree;
        })
      );
    }
  }, [productsNumberByCategory]);

  return {
    trees,
    loadingStatus,
    loadTrees,
    loadingError,
  };
};

export {useCategoryTreeList};

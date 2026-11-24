import {useCountProductsByCategory} from './useCountProductsByCategory';
import {useEffect, useState} from 'react';

type CategoryDeletion = {
  callback: (nbProducts: number) => void;
};

// Wrap the category deletion to count its number of products at the last moment.
//
// One effect rather than a 'pending' -> 'ready' status split over two: the count being known IS
// the ready state. Two effects each reading the other's state could not list their dependencies
// without the first one re-running while still 'pending', and fetching the count twice.
const useCountProductsBeforeDeleteCategory = (categoryId: number) => {
  const [categoryDeletion, setCategoryDeletion] = useState<CategoryDeletion | null>(null);
  const {numberOfProducts, loadNumberOfProducts} = useCountProductsByCategory(categoryId);

  const beforeDelete = (deleteCategory: (nbProducts: number) => void) => {
    setCategoryDeletion({callback: deleteCategory});
  };

  useEffect(() => {
    if (categoryDeletion === null) {
      return;
    }

    if (numberOfProducts === null) {
      loadNumberOfProducts();
      return;
    }

    categoryDeletion.callback(numberOfProducts as number);
    setCategoryDeletion(null);
  }, [categoryDeletion, numberOfProducts, loadNumberOfProducts]);

  return beforeDelete;
};

export {useCountProductsBeforeDeleteCategory};

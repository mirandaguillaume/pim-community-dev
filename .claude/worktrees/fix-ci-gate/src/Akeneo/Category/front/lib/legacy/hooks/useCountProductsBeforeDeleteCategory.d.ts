declare const useCountProductsBeforeDeleteCategory: (categoryId: number) => (deleteCategory: (nbProducts: number) => void) => void;
export { useCountProductsBeforeDeleteCategory };

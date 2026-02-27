declare const useCountProductsByCategory: (categoryId: number) => {
    numberOfProducts: unknown;
    loadNumberOfProducts: () => Promise<void>;
};
export { useCountProductsByCategory };

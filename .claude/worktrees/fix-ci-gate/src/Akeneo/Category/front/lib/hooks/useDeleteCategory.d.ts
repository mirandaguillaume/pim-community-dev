declare type CategoryToDelete = {
    identifier: number;
    label: string;
    code: string;
    numberOfProducts: number;
    onDelete: () => void;
};
declare const useDeleteCategory: () => {
    isCategoryDeletionPossible: (label: string, numberOfProducts: number) => boolean;
    handleDeleteCategory: (categoryToDelete: CategoryToDelete) => Promise<void>;
};
export { useDeleteCategory };
export type { CategoryToDelete };

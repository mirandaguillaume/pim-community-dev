declare type CategoryToDelete = {
    identifier: number;
    label: string;
    onDelete: () => void;
};
declare const useDeleteCategory: () => {
    isCategoryDeletionPossible: (label: string, numberOfProducts: number) => boolean;
    handleDeleteCategory: (categoryToDelete: CategoryToDelete) => Promise<void>;
};
export { useDeleteCategory };
export type { CategoryToDelete };

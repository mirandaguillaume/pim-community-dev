declare type CatalogLocale = {
    id: number;
    code: string;
    label: string;
    region: string;
    language: string;
};
declare const useCatalogLocales: () => CatalogLocale[] | undefined;
export { useCatalogLocales };

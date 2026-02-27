declare type UiLocale = {
    id: number;
    code: string;
    label: string;
    region: string;
    language: string;
};
declare const useUiLocales: () => UiLocale[] | undefined;
export { useUiLocales };

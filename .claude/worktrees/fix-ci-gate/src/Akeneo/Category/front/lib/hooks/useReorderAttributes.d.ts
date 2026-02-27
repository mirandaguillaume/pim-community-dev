declare type ReorderedAttributes = {
    templateUuid: string;
    uuids: string[];
};
export declare const useReorderAttributes: () => import("react-query").UseMutationResult<void, void, ReorderedAttributes, unknown>;
export {};

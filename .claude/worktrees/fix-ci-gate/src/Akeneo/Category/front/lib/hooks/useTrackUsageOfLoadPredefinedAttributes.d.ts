declare type Action = 'load_predefined_attributes' | 'create_first_attribute';
export declare const useTrackUsageOfLoadPredefinedAttributes: (templateUuid: string) => import("react-query").UseMutateFunction<void, unknown, Action, unknown>;
export {};

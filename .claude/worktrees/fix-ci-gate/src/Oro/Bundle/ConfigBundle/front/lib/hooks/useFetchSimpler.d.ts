export interface FetchResultIdle {
    type: 'idle';
}
export interface FetchResultFetching {
    type: 'fetching';
}
export interface FetchResultError {
    type: 'error';
    message: string;
}
export interface FetchResultFetched<Payload> {
    type: 'fetched';
    payload: Payload;
}
export declare type FetchResult<Payload> = FetchResultIdle | FetchResultFetching | FetchResultError | FetchResultFetched<Payload>;
export declare type FetchHookResult<FrontendPayload> = [result: FetchResult<FrontendPayload>, fetch: () => Promise<void>];
declare const useFetchSimpler: <BackendPayload, FrontendPayload = BackendPayload>(url: string, payloadConverter?: (payload: BackendPayload) => FrontendPayload, init?: RequestInit | undefined) => FetchHookResult<FrontendPayload>;
export { useFetchSimpler };

export declare class BadRequestError<T> extends Error {
    readonly data: T;
    constructor(data: T);
}
export declare class ForbiddenError extends Error {
    constructor();
}
export declare class UnauthorizedError extends Error {
    constructor();
}
export declare type ApiError<T> = BadRequestError<T> | UnauthorizedError | ForbiddenError | Error;
export declare const apiFetch: <T = void, E = unknown>(url: string, init: RequestInit) => Promise<T>;

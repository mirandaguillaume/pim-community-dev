import { Component } from 'react';
export declare class ErrorBoundary extends Component<unknown, {
    error?: Error;
}> {
    constructor(props: unknown);
    static getDerivedStateFromError(error: unknown): {
        error: unknown;
    };
    render(): import("react").ReactNode;
}

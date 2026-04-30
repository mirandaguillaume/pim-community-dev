import {useDateFormatter} from '@src/shared/formatter/use-date-formatter';
import {UserContext} from '@src/shared/user';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const makeUser = (uiLocale: string, timezone: string) => ({
    get: <T>(key: string): T => (({uiLocale, timezone}) as Record<string, unknown>)[key] as T,
    set: () => undefined,
    refresh: () => Promise.resolve(),
});

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(UserContext.Provider, {value: makeUser('en_US', 'UTC')}, children);

describe('useDateFormatter', () => {
    it('formats a date string with the user locale and timezone', () => {
        const {result} = renderHook(() => useDateFormatter(), {wrapper});
        const formatted = result.current('2024-06-15T12:00:00Z', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        });
        expect(typeof formatted).toBe('string');
        expect(formatted).toMatch(/2024/);
    });

    it('returns a stable callback reference across re-renders', () => {
        const {result, rerender} = renderHook(() => useDateFormatter(), {wrapper});
        const first = result.current;
        rerender();
        expect(result.current).toBe(first);
    });

    it('falls back to UTC when the timezone is invalid', () => {
        const badWrapper: FC<PropsWithChildren> = ({children}) =>
            React.createElement(UserContext.Provider, {value: makeUser('en_US', 'Invalid/Zone')}, children);
        const {result} = renderHook(() => useDateFormatter(), {wrapper: badWrapper});
        expect(() => result.current('2024-06-15T12:00:00Z')).not.toThrow();
    });
});

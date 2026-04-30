import {useNumberFormatter} from '@src/shared/formatter/use-number-formatter';
import {UserContext} from '@src/shared/user';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const makeUser = (uiLocale: string) => ({
    get: <T>(key: string): T => (({uiLocale}) as Record<string, unknown>)[key] as T,
    set: () => undefined,
    refresh: () => Promise.resolve(),
});

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(UserContext.Provider, {value: makeUser('en_US')}, children);

describe('useNumberFormatter', () => {
    it('formats a number with the user locale', () => {
        const {result} = renderHook(() => useNumberFormatter(), {wrapper});
        const formatted = result.current(1234567.89);
        expect(typeof formatted).toBe('string');
        expect(formatted).toMatch(/1[,.]?234/);
    });

    it('respects Intl.NumberFormatOptions (currency)', () => {
        const {result} = renderHook(() => useNumberFormatter(), {wrapper});
        const formatted = result.current(42, {style: 'currency', currency: 'USD'});
        expect(formatted).toMatch(/42/);
        expect(formatted).toMatch(/\$/);
    });

    it('returns a stable callback reference across re-renders', () => {
        const {result, rerender} = renderHook(() => useNumberFormatter(), {wrapper});
        const first = result.current;
        rerender();
        expect(result.current).toBe(first);
    });
});

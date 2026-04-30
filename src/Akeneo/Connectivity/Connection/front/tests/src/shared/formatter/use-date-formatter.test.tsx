import {renderHook} from '@testing-library/react';
import {useDateFormatter} from '@src/shared/formatter/use-date-formatter';
import {ProvidersWithoutRouter} from '../../../test-utils';

describe('useDateFormatter', () => {
    it('formats a date string using the user locale and timezone', () => {
        const {result} = renderHook(() => useDateFormatter(), {wrapper: ProvidersWithoutRouter});

        const formatted = result.current('2024-01-15T10:30:00+00:00');

        expect(typeof formatted).toBe('string');
        expect(formatted.length).toBeGreaterThan(0);
    });

    it('accepts custom Intl.DateTimeFormatOptions', () => {
        const {result} = renderHook(() => useDateFormatter(), {wrapper: ProvidersWithoutRouter});

        const formatted = result.current('2024-06-20T00:00:00+00:00', {month: 'long', year: 'numeric'});

        expect(formatted).toContain('2024');
    });

    it('returns a stable callback reference across re-renders', () => {
        const {result, rerender} = renderHook(() => useDateFormatter(), {wrapper: ProvidersWithoutRouter});
        const first = result.current;

        rerender();

        expect(result.current).toBe(first);
    });
});

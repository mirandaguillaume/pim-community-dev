import {renderHook} from '@testing-library/react';
import {useNumberFormatter} from '@src/shared/formatter/use-number-formatter';
import {ProvidersWithoutRouter} from '../../../test-utils';

describe('useNumberFormatter', () => {
    it('formats a number using the user locale', () => {
        const {result} = renderHook(() => useNumberFormatter(), {wrapper: ProvidersWithoutRouter});

        const formatted = result.current(1234567);

        expect(typeof formatted).toBe('string');
        expect(formatted).toContain('1');
    });

    it('accepts custom Intl.NumberFormatOptions', () => {
        const {result} = renderHook(() => useNumberFormatter(), {wrapper: ProvidersWithoutRouter});

        const formatted = result.current(0.5, {style: 'percent'});

        expect(formatted).toContain('50');
    });

    it('returns a stable callback reference across re-renders', () => {
        const {result, rerender} = renderHook(() => useNumberFormatter(), {wrapper: ProvidersWithoutRouter});
        const first = result.current;

        rerender();

        expect(result.current).toBe(first);
    });
});

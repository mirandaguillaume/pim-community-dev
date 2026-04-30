import {useDebounceCallback} from '@src/shared/utils/use-debounce-callback';
import {renderHook} from '@testing-library/react';

describe('useDebounceCallback', () => {
    beforeEach(() => {
        jest.useFakeTimers();
    });

    afterEach(() => {
        jest.useRealTimers();
    });

    it('delays callback invocation by the specified delay', () => {
        const fn = jest.fn();
        const {result} = renderHook(() => useDebounceCallback(fn, 150));

        result.current('hello');
        expect(fn).not.toHaveBeenCalled();

        jest.advanceTimersByTime(150);
        expect(fn).toHaveBeenCalledWith('hello');
    });

    it('returns the same callback reference when inputs are stable', () => {
        const fn = jest.fn();
        const {result, rerender} = renderHook(() => useDebounceCallback(fn, 100));

        const first = result.current;
        rerender();
        expect(result.current).toBe(first);
    });

    it('debounces rapid calls into a single invocation', () => {
        const fn = jest.fn();
        const {result} = renderHook(() => useDebounceCallback(fn, 200));

        result.current('a');
        result.current('b');
        result.current('c');

        jest.advanceTimersByTime(200);
        expect(fn).toHaveBeenCalledTimes(1);
        expect(fn).toHaveBeenCalledWith('c');
    });
});

import {renderHook, act} from '@testing-library/react';
import {useToggleState} from '@src/shared/hooks/useToggleState';

describe('useToggleState', () => {
    it('returns the default value', () => {
        const {result} = renderHook(() => useToggleState(false));
        expect(result.current[0]).toBe(false);
    });

    it('returns true when initialized with true', () => {
        const {result} = renderHook(() => useToggleState(true));
        expect(result.current[0]).toBe(true);
    });

    it('sets value to true when calling setTrue', () => {
        const {result} = renderHook(() => useToggleState(false));
        act(() => result.current[1]());
        expect(result.current[0]).toBe(true);
    });

    it('sets value to false when calling setFalse', () => {
        const {result} = renderHook(() => useToggleState(true));
        act(() => result.current[2]());
        expect(result.current[0]).toBe(false);
    });

    it('setTrue and setFalse are stable references across renders', () => {
        const {result, rerender} = renderHook(() => useToggleState(false));
        const [, setTrue, setFalse] = result.current;
        rerender();
        expect(result.current[1]).toBe(setTrue);
        expect(result.current[2]).toBe(setFalse);
    });
});

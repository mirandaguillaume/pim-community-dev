import {act, renderHook} from '@testing-library/react';
import {useDebounce, useDebounceCallback} from './useDebounce';

describe('useDebounce', () => {
  beforeEach(() => {
    jest.useFakeTimers();
  });
  afterAll(() => {
    jest.useRealTimers();
  });

  it('returns the initial value immediately', () => {
    const {result} = renderHook(() => useDebounce('hello', 100));
    expect(result.current).toBe('hello');
  });

  it('debounces rapid value changes and resolves to the last value', () => {
    const {result, rerender} = renderHook(
      ({value, delay}: {value: string; delay: number}) => useDebounce(value, delay),
      {initialProps: {value: 'initial', delay: 100}}
    );

    expect(result.current).toBe('initial');

    // Simulate rapid typing — each rerender resets the debounce timer
    const delay = 100;
    act(() => {
      rerender({value: 't', delay});
    });
    act(() => {
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('initial');

    act(() => {
      rerender({value: 'ty', delay});
    });
    act(() => {
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('initial');

    act(() => {
      rerender({value: 'typ', delay});
    });
    act(() => {
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('initial');

    act(() => {
      rerender({value: 'typing', delay});
    });
    act(() => {
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('initial');

    // After full delay elapses, debounced value updates to latest
    act(() => {
      jest.advanceTimersByTime(100);
    });
    expect(result.current).toBe('typing');
  });
});

describe('useDebounceCallback', () => {
  beforeEach(() => {
    jest.useFakeTimers();
  });
  afterAll(() => {
    jest.useRealTimers();
  });

  it('use the debounced callback', () => {
    const callback = jest.fn();

    const {result} = renderHook(() => useDebounceCallback(callback, 100));

    act(() => {
      result.current();
      jest.advanceTimersByTime(100);
    });
    expect(callback).toBeCalled();
  });

  it('use the debounced callback after 100ms', () => {
    type TestedCallback = (value: string) => void;
    const callback: TestedCallback = jest.fn();

    const {result} = renderHook(() => useDebounceCallback(callback, 100));

    act(() => {
      result.current('t');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('ty');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('typ');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('typi');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('typin');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      result.current('typing');
      jest.advanceTimersByTime(10);
    });
    expect(callback).not.toBeCalled();

    act(() => {
      jest.advanceTimersByTime(100);
    });
    expect(callback).toBeCalledWith('typing');
  });
});

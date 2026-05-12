import {renderHook} from '@testing-library/react';
import {useDebounceCallback} from './useDebounceCallback';

describe('useDebounceCallback', () => {
  beforeEach(() => {
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('returns a callable function', () => {
    const {result} = renderHook(() => useDebounceCallback(jest.fn(), 300));
    expect(typeof result.current).toBe('function');
  });

  it('does not invoke the callback synchronously', () => {
    const callback = jest.fn();
    const {result} = renderHook(() => useDebounceCallback(callback, 300));

    result.current();

    expect(callback).not.toHaveBeenCalled();
  });

  it('invokes the callback after the specified delay', () => {
    const callback = jest.fn();
    const {result} = renderHook(() => useDebounceCallback(callback, 300));

    result.current();
    jest.advanceTimersByTime(300);

    expect(callback).toHaveBeenCalledTimes(1);
  });

  it('forwards arguments to the callback', () => {
    const callback = jest.fn();
    const {result} = renderHook(() => useDebounceCallback(callback, 100));

    result.current('hello', 42);
    jest.advanceTimersByTime(100);

    expect(callback).toHaveBeenCalledWith('hello', 42);
  });

  it('debounces: multiple rapid calls produce a single invocation', () => {
    const callback = jest.fn();
    const {result} = renderHook(() => useDebounceCallback(callback, 200));

    result.current('first');
    jest.advanceTimersByTime(100);
    result.current('second');
    jest.advanceTimersByTime(100);
    result.current('third');
    jest.advanceTimersByTime(200);

    expect(callback).toHaveBeenCalledTimes(1);
    expect(callback).toHaveBeenCalledWith('third');
  });

  it('fires again after the quiet period resets', () => {
    const callback = jest.fn();
    const {result} = renderHook(() => useDebounceCallback(callback, 150));

    result.current('a');
    jest.advanceTimersByTime(150);

    result.current('b');
    jest.advanceTimersByTime(150);

    expect(callback).toHaveBeenCalledTimes(2);
    expect(callback).toHaveBeenNthCalledWith(1, 'a');
    expect(callback).toHaveBeenNthCalledWith(2, 'b');
  });
});

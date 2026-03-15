import {act, renderHook} from '@testing-library/react';
import {useDebounce} from './useDebounce';

describe('useDebounce', () => {
  beforeEach(() => {
    jest.useFakeTimers();
  });
  afterAll(() => {
    jest.useRealTimers();
  });

  it('use the debounced value after 100ms', () => {
    const {result, rerender} = renderHook(
      ({value, delay}: {value: string; delay: number}) => useDebounce(value, delay),
      {initialProps: {value: '', delay: 100}}
    );

    expect(result.current).toBe('');

    const delay = 100;

    // Simulate rapid typing: each keystroke restarts the debounce timer,
    // so the debounced value stays at the initial value until typing stops.
    rerender({value: 't', delay});
    act(() => jest.advanceTimersByTime(10));
    expect(result.current).toBe('');

    rerender({value: 'ty', delay});
    act(() => jest.advanceTimersByTime(10));
    expect(result.current).toBe('');

    rerender({value: 'typ', delay});
    act(() => jest.advanceTimersByTime(10));
    expect(result.current).toBe('');

    rerender({value: 'typi', delay});
    act(() => jest.advanceTimersByTime(10));
    expect(result.current).toBe('');

    rerender({value: 'typin', delay});
    act(() => jest.advanceTimersByTime(10));
    expect(result.current).toBe('');

    rerender({value: 'typing', delay});
    act(() => jest.advanceTimersByTime(10));
    expect(result.current).toBe('');

    // After 100ms with no new input, the debounced value updates
    act(() => jest.advanceTimersByTime(100));
    expect(result.current).toBe('typing');
  });
});

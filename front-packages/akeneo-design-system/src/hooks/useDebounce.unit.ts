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
    const {result, rerender} = renderHook<string, {value: string; delay: number}>(
      ({value, delay}) => useDebounce(value, delay),
      {initialProps: {value: '', delay: 0}}
    );

    const delay = 100;
    void act(() => {
      rerender({value: 't', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    void act(() => {
      rerender({value: 'ty', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    void act(() => {
      rerender({value: 'typ', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    void act(() => {
      rerender({value: 'typi', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    void act(() => {
      rerender({value: 'typin', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    void act(() => {
      rerender({value: 'typing', delay});
      jest.advanceTimersByTime(10);
    });
    expect(result.current).toBe('t');

    void act(() => {
      jest.advanceTimersByTime(100);
    });
    expect(result.current).toBe('typing');
  });
});

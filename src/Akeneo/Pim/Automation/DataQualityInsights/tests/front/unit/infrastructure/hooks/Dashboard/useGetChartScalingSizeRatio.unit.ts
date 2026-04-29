import {renderHook, act} from '@testing-library/react';
import useGetChartScalingSizeRatio from '../../../../../../front/src/infrastructure/hooks/Dashboard/useGetChartScalingSizeRatio';

describe('useGetChartScalingSizeRatio', () => {
  let rafSpy: jest.SpyInstance;

  beforeEach(() => {
    rafSpy = jest.spyOn(window, 'requestAnimationFrame').mockImplementation(cb => {
      cb(0);
      return 0;
    });
  });

  afterEach(() => {
    rafSpy.mockRestore();
  });

  it('returns ratio 1 when ref is null', () => {
    const ref = {current: null};
    const {result} = renderHook(() => useGetChartScalingSizeRatio(ref as any, 600));
    expect(result.current).toEqual({upScalingRatio: 1, downScalingRatio: 1});
  });

  it('returns ratio 1 when container width does not exceed initialWidth', () => {
    const ref = {current: {getBoundingClientRect: () => ({width: 500})}};
    const {result} = renderHook(() => useGetChartScalingSizeRatio(ref as any, 600));
    expect(result.current).toEqual({upScalingRatio: 1, downScalingRatio: 1});
  });

  it('computes scale ratio when container is wider than initialWidth', () => {
    const ref = {current: {getBoundingClientRect: () => ({width: 1200})}};
    const {result} = renderHook(() => useGetChartScalingSizeRatio(ref as any, 600));
    expect(result.current.upScalingRatio).toBe(2);
    expect(result.current.downScalingRatio).toBeCloseTo(0.5);
  });

  it('updates ratio on window resize', () => {
    const rect = {width: 600};
    const ref = {current: {getBoundingClientRect: () => ({...rect})}};
    const {result} = renderHook(() => useGetChartScalingSizeRatio(ref as any, 600));

    expect(result.current.upScalingRatio).toBe(1);

    act(() => {
      rect.width = 1200;
      ref.current = {getBoundingClientRect: () => ({...rect})};
      window.dispatchEvent(new Event('resize'));
    });

    expect(result.current.upScalingRatio).toBe(2);
  });

  it('removes resize listener on unmount', () => {
    const removeSpy = jest.spyOn(window, 'removeEventListener');
    const ref = {current: {getBoundingClientRect: () => ({width: 600})}};
    const {unmount} = renderHook(() => useGetChartScalingSizeRatio(ref as any, 600));

    unmount();
    expect(removeSpy).toHaveBeenCalledWith('resize', expect.any(Function));
    removeSpy.mockRestore();
  });
});

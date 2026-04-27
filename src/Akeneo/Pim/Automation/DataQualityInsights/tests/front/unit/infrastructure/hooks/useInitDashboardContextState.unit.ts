import {act, renderHook} from '@testing-library/react';
import {useInitDashboardContextState} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/useInitDashboardContextState';
import {
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY,
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY,
} from '@akeneo-pim-community/data-quality-insights/src/application/constant';

describe('useInitDashboardContextState', () => {
  let dispatchEventSpy: jest.SpyInstance;

  beforeEach(() => {
    dispatchEventSpy = jest.spyOn(window, 'dispatchEvent');
  });

  afterEach(() => {
    dispatchEventSpy.mockRestore();
  });

  it('initialises state from the props passed at mount', () => {
    const {result} = renderHook(() => useInitDashboardContextState('cameras', null));
    expect(result.current.familyCode).toBe('cameras');
    expect(result.current.category).toBeNull();
  });

  it('exposes an updateDashboardFilters callback', () => {
    const {result} = renderHook(() => useInitDashboardContextState(null, null));
    expect(result.current.updateDashboardFilters).toBeInstanceOf(Function);
  });

  it('dispatches FILTER_FAMILY custom event when category is null', () => {
    const {result} = renderHook(() => useInitDashboardContextState(null, null));

    act(() => {
      result.current.updateDashboardFilters('cameras', null);
    });

    expect(dispatchEventSpy).toHaveBeenCalledWith(
      expect.objectContaining({type: DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY})
    );
    expect(result.current.familyCode).toBe('cameras');
    expect(result.current.category).toBeNull();
  });

  it('dispatches FILTER_CATEGORY custom event when a category is provided', () => {
    const {result} = renderHook(() => useInitDashboardContextState(null, null));
    const category = {code: 'master', id: 1, rootCategoryId: 1};

    act(() => {
      result.current.updateDashboardFilters(null, category);
    });

    expect(dispatchEventSpy).toHaveBeenCalledWith(
      expect.objectContaining({type: DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY})
    );
    expect(result.current.category).toEqual(category);
  });

  it('updates familyCode state when updateDashboardFilters is called', () => {
    const {result} = renderHook(() => useInitDashboardContextState(null, null));

    act(() => {
      result.current.updateDashboardFilters('bags', null);
    });

    expect(result.current.familyCode).toBe('bags');
  });
});

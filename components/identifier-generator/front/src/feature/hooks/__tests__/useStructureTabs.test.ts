import {renderHook, act} from '@testing-library/react';
import {useStructureTabs} from '../useStructureTabs';
import {GeneratorTab} from '../../models';

const LOCAL_STORAGE_KEY = 'identifier-generator.currentTab';

describe('useStructureTabs', () => {
  beforeEach(() => {
    localStorage.clear();
  });

  it('should default to GeneratorTab.GENERAL when localStorage is empty', () => {
    const {result} = renderHook(() => useStructureTabs());
    expect(result.current.currentTab).toBe(GeneratorTab.GENERAL);
  });

  it('should restore tab from localStorage on mount', () => {
    localStorage.setItem(LOCAL_STORAGE_KEY, GeneratorTab.STRUCTURE);
    const {result} = renderHook(() => useStructureTabs());
    expect(result.current.currentTab).toBe(GeneratorTab.STRUCTURE);
  });

  it('should restore PRODUCT_SELECTION tab from localStorage', () => {
    localStorage.setItem(LOCAL_STORAGE_KEY, GeneratorTab.PRODUCT_SELECTION);
    const {result} = renderHook(() => useStructureTabs());
    expect(result.current.currentTab).toBe(GeneratorTab.PRODUCT_SELECTION);
  });

  it('should fall back to GeneratorTab.GENERAL for invalid localStorage value', () => {
    localStorage.setItem(LOCAL_STORAGE_KEY, 'invalid_tab_value');
    const {result} = renderHook(() => useStructureTabs());
    expect(result.current.currentTab).toBe(GeneratorTab.GENERAL);
  });

  it('should update currentTab and save to localStorage on setCurrentTab', () => {
    const {result} = renderHook(() => useStructureTabs());

    act(() => {
      result.current.setCurrentTab(GeneratorTab.STRUCTURE);
    });

    expect(result.current.currentTab).toBe(GeneratorTab.STRUCTURE);
    expect(localStorage.getItem(LOCAL_STORAGE_KEY)).toBe(GeneratorTab.STRUCTURE);
  });

  it('should persist tab across re-renders', () => {
    const {result, rerender} = renderHook(() => useStructureTabs());

    act(() => {
      result.current.setCurrentTab(GeneratorTab.PRODUCT_SELECTION);
    });

    rerender();

    expect(result.current.currentTab).toBe(GeneratorTab.PRODUCT_SELECTION);
  });
});

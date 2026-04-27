import {act, renderHook} from '@testing-library/react';
import useAttributeContextState from 'akeneopimstructure/js/attribute-option/hooks/useAttributeContextState';
import {ATTRIBUTE_OPTIONS_AUTO_SORT} from 'akeneopimstructure/js/attribute-option/model';

beforeEach(() => {
  jest.spyOn(window, 'dispatchEvent').mockImplementation(() => true);
});

afterEach(() => jest.restoreAllMocks());

test('it exposes attributeId and initial autoSortOptions', () => {
  const {result} = renderHook(() => useAttributeContextState(42, false));
  expect(result.current.attributeId).toBe(42);
  expect(result.current.autoSortOptions).toBe(false);
});

test('it initialises autoSortOptions to true when passed true', () => {
  const {result} = renderHook(() => useAttributeContextState(1, true));
  expect(result.current.autoSortOptions).toBe(true);
});

test('it toggles autoSortOptions from false to true and dispatches a CustomEvent', () => {
  const {result} = renderHook(() => useAttributeContextState(1, false));

  act(() => {
    result.current.toggleAutoSortOptions();
  });

  expect(result.current.autoSortOptions).toBe(true);
  expect(window.dispatchEvent).toHaveBeenCalledWith(
    expect.objectContaining({
      type: ATTRIBUTE_OPTIONS_AUTO_SORT,
      detail: {autoSortOptions: true},
    })
  );
});

test('it toggles autoSortOptions from true back to false', () => {
  const {result} = renderHook(() => useAttributeContextState(1, true));

  act(() => {
    result.current.toggleAutoSortOptions();
  });

  expect(result.current.autoSortOptions).toBe(false);
  expect(window.dispatchEvent).toHaveBeenCalledWith(
    expect.objectContaining({
      type: ATTRIBUTE_OPTIONS_AUTO_SORT,
      detail: {autoSortOptions: false},
    })
  );
});

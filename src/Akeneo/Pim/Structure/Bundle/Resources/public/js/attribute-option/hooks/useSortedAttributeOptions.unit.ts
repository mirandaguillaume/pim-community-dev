import {renderHook} from '@testing-library/react';
import {useSortedAttributeOptions} from 'akeneopimstructure/js/attribute-option/hooks/useSortedAttributeOptions';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';

const makeOption = (id: number, code: string): AttributeOption => ({
  id,
  code,
  optionValues: {},
  toImprove: undefined,
});

test('it returns null when attributeOptions is null', () => {
  const {result} = renderHook(() => useSortedAttributeOptions(null, false));
  expect(result.current.sortedAttributeOptions).toBeNull();
});

test('it preserves original order when autoSortOptions is false', () => {
  const options = [makeOption(2, 'zebra'), makeOption(1, 'apple')];
  const {result} = renderHook(() => useSortedAttributeOptions(options, false));
  expect(result.current.sortedAttributeOptions?.map(o => o.code)).toEqual(['zebra', 'apple']);
});

test('it sorts options alphabetically when autoSortOptions is true', () => {
  const options = [makeOption(3, 'zebra'), makeOption(1, 'apple'), makeOption(2, 'mango')];
  const {result} = renderHook(() => useSortedAttributeOptions(options, true));
  expect(result.current.sortedAttributeOptions?.map(o => o.code)).toEqual(['apple', 'mango', 'zebra']);
});

test('it does not mutate the original array when sorting', () => {
  const options = [makeOption(2, 'zebra'), makeOption(1, 'apple')];
  renderHook(() => useSortedAttributeOptions(options, true));
  expect(options[0].code).toBe('zebra');
});

test('it exposes a setSortedAttributeOptions setter', () => {
  const options = [makeOption(1, 'alpha')];
  const {result} = renderHook(() => useSortedAttributeOptions(options, false));
  expect(typeof result.current.setSortedAttributeOptions).toBe('function');
});

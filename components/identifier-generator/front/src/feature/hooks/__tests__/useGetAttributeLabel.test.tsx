import {renderHook, waitFor} from '@testing-library/react';
import {useGetAttributeLabel} from '../useGetAttributeLabel';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {Attribute, ATTRIBUTE_TYPE} from '../../models';

const attribute: Attribute = {
  code: 'sku',
  labels: {en_US: 'SKU Label'},
  localizable: false,
  scopable: false,
  type: ATTRIBUTE_TYPE.TEXT,
};

describe('useGetAttributeLabel', () => {
  it('should return the label for the catalog locale when attribute is found', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve(attribute),
      status: 200,
      statusText: '',
    } as Response);

    const {result} = renderHook(() => useGetAttributeLabel('sku'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current).toBe('SKU Label');
    });
  });

  it('should return [code] when no label exists for the locale', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({...attribute, labels: {}}),
      status: 200,
      statusText: '',
    } as Response);

    const {result} = renderHook(() => useGetAttributeLabel('sku'), {wrapper: createWrapper()});

    await waitFor(() => {
      // After data loads, label should be [sku] since en_US label is missing
      expect(result.current).toBe('[sku]');
    });
  });

  it('should return [code] when attribute code is undefined', () => {
    const {result} = renderHook(() => useGetAttributeLabel(undefined), {wrapper: createWrapper()});
    expect(result.current).toBe('[undefined]');
  });
});

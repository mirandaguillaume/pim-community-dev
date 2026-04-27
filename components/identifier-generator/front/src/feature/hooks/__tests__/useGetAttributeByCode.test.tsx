import {renderHook, waitFor} from '@testing-library/react';
import {useGetAttributeByCode} from '../useGetAttributeByCode';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {AttributeNotFound, ServerError, Unauthorized} from '../../errors';
import {Attribute, ATTRIBUTE_TYPE} from '../../models';

const attribute: Attribute = {
  code: 'sku',
  labels: {en_US: 'SKU'},
  localizable: false,
  scopable: false,
  type: ATTRIBUTE_TYPE.TEXT,
};

describe('useGetAttributeByCode', () => {
  it('should return attribute data on success', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve(attribute),
      status: 200,
      statusText: '',
    } as Response);

    const {result} = renderHook(() => useGetAttributeByCode('sku'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.data).not.toBeUndefined();
    });

    expect(result.current.data).toEqual(attribute);
    expect(result.current.error).toBeNull();
  });

  it('should throw Unauthorized on 401', async () => {
    jest.spyOn(console, 'error').mockImplementation(() => null);
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({}),
      status: 401,
      statusText: 'Unauthorized',
    } as Response);

    const {result} = renderHook(() => useGetAttributeByCode('sku'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.error).not.toBeNull();
    });

    expect(result.current.error).toBeInstanceOf(Unauthorized);
  });

  it('should throw AttributeNotFound on 404', async () => {
    jest.spyOn(console, 'error').mockImplementation(() => null);
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({}),
      status: 404,
      statusText: 'Not Found',
    } as Response);

    const {result} = renderHook(() => useGetAttributeByCode('sku'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.error).not.toBeNull();
    });

    expect(result.current.error).toBeInstanceOf(AttributeNotFound);
  });

  it('should throw ServerError on other errors', async () => {
    jest.spyOn(console, 'error').mockImplementation(() => null);
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({}),
      status: 500,
      statusText: 'Internal Server Error',
    } as Response);

    const {result} = renderHook(() => useGetAttributeByCode('sku'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.error).not.toBeNull();
    });

    expect(result.current.error).toBeInstanceOf(ServerError);
  });

  it('should not fetch when attributeCode is undefined', () => {
    const {result} = renderHook(() => useGetAttributeByCode(undefined), {wrapper: createWrapper()});

    expect(result.current.isLoading).toBe(false);
    expect(result.current.data).toBeUndefined();
    expect(fetch).not.toHaveBeenCalled();
  });
});

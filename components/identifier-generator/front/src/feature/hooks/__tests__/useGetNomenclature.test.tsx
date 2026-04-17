import {renderHook, waitFor} from '@testing-library/react';
import {useGetNomenclature} from '../useGetNomenclature';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {ServerError} from '../../errors';
import {Operator} from '../../models';

describe('useGetNomenclature', () => {
  it('should return nomenclature data and convert null generate_if_empty to false', async () => {
    mockResponse('akeneo_identifier_generator_nomenclature_rest_get', 'GET', {
      ok: true,
      json: {
        propertyCode: 'family',
        operator: Operator.EQUALS,
        generate_if_empty: null,
        value: 3,
        values: {},
      },
    });

    const {result} = renderHook(() => useGetNomenclature('family'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.data).not.toBeUndefined();
    });

    expect(result.current.data?.generate_if_empty).toBe(false);
    expect(result.current.data?.propertyCode).toBe('family');
  });

  it('should preserve generate_if_empty when true', async () => {
    mockResponse('akeneo_identifier_generator_nomenclature_rest_get', 'GET', {
      ok: true,
      json: {
        propertyCode: 'family',
        operator: Operator.LOWER_OR_EQUAL_THAN,
        generate_if_empty: true,
        value: 5,
        values: {},
      },
    });

    const {result} = renderHook(() => useGetNomenclature('family'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.data).not.toBeUndefined();
    });

    expect(result.current.data?.generate_if_empty).toBe(true);
  });

  it('should throw ServerError when response is not ok', async () => {
    jest.spyOn(console, 'error').mockImplementation(() => null);
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({}),
      status: 500,
      statusText: 'Internal Server Error',
    } as Response);

    const {result} = renderHook(() => useGetNomenclature('family'), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.error).not.toBeNull();
    });

    expect(result.current.error).toBeInstanceOf(ServerError);
  });
});

import {act, renderHook, waitFor} from '@testing-library/react';
import {useSaveNomenclature} from '../useSaveNomenclature';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {Nomenclature, Operator} from '../../models';

const nomenclature: Nomenclature = {
  propertyCode: 'family',
  operator: Operator.EQUALS,
  generate_if_empty: false,
  value: 3,
  values: {shirts: 'SHT', trousers: 'TRS'},
};

describe('useSaveNomenclature', () => {
  it('should save nomenclature successfully', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve(nomenclature),
      status: 200,
      statusText: '',
    } as Response);

    const {result} = renderHook(() => useSaveNomenclature(), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.save).toBeDefined();
    });

    act(() => {
      result.current.save(nomenclature);
    });

    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });
  });

  it('should reject with violations on error response', async () => {
    const violations = [{path: 'value', message: 'Value must be positive'}];

    jest.spyOn(console, 'error').mockImplementation(() => null);
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve(violations),
      status: 422,
      statusText: 'Unprocessable Entity',
    } as Response);

    const {result} = renderHook(() => useSaveNomenclature(), {wrapper: createWrapper()});

    await waitFor(() => {
      expect(result.current.save).toBeDefined();
    });

    let capturedError: unknown = null;

    act(() => {
      result.current.save(nomenclature, {
        onError: (error: unknown) => {
          capturedError = error;
        },
      });
    });

    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });

    await waitFor(() => {
      expect(capturedError).toEqual(violations);
    });
  });

  it('should initially not be loading', () => {
    const {result} = renderHook(() => useSaveNomenclature(), {wrapper: createWrapper()});
    expect(result.current.isLoading).toBe(false);
  });
});

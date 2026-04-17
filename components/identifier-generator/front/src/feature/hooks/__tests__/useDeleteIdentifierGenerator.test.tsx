import {act, renderHook, waitFor} from '@testing-library/react';
import {useDeleteIdentifierGenerator} from '../useDeleteIdentifierGenerator';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {ServerError} from '../../errors';

describe('useDeleteIdentifierGenerator', () => {
  it('should delete a generator successfully', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({}),
      status: 204,
      statusText: '',
    } as Response);

    const {result} = renderHook(() => useDeleteIdentifierGenerator(), {wrapper: createWrapper()});

    act(() => {
      result.current.mutate('my_generator');
    });

    await waitFor(() => {
      expect(fetch).toHaveBeenCalled();
    });

    expect(result.current.isError).toBe(false);
  });

  it('should throw ServerError when response is not ok', async () => {
    jest.spyOn(console, 'error').mockImplementation(() => null);
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({}),
      status: 500,
      statusText: 'Internal Server Error',
    } as Response);

    const {result} = renderHook(() => useDeleteIdentifierGenerator(), {wrapper: createWrapper()});

    act(() => {
      result.current.mutate('my_generator');
    });

    await waitFor(() => {
      expect(result.current.isError).toBe(true);
    });

    expect(result.current.error).toBeInstanceOf(ServerError);
  });
});

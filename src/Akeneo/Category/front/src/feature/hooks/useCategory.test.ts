import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {act} from '@testing-library/react';
import {useCategory} from './useCategory';

describe('useCategory', () => {
  test('it returns idle status and null category initially', () => {
    const {result} = renderHookWithProviders(() => useCategory(1));
    expect(result.current.category).toBeNull();
    expect(result.current.status).toBe('idle');
    expect(result.current.error).toBeNull();
    expect(result.current.load).toBeInstanceOf(Function);
  });

  test('it fetches and returns the category on load', async () => {
    const mockCategory = {id: 1, code: 'master', labels: {en_US: 'Master catalog'}};
    jest.spyOn(global, 'fetch').mockResolvedValueOnce({
      json: () => Promise.resolve(mockCategory),
    } as any);

    const {result} = renderHookWithProviders(() => useCategory(1));

    await act(async () => {
      result.current.load();
    });

    expect(result.current.category).toEqual(mockCategory);
    expect(result.current.status).toBe('fetched');
  });

  test('it sets error status when fetch fails', async () => {
    jest.spyOn(global, 'fetch').mockRejectedValueOnce(new Error('Network error'));

    const {result} = renderHookWithProviders(() => useCategory(1));

    await act(async () => {
      result.current.load();
    });

    expect(result.current.status).toBe('error');
    expect(result.current.error).toMatch(/Network error/);
  });
});

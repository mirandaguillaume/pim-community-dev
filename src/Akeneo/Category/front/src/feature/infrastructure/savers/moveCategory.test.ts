import {moveCategory} from './moveCategory';
import {Router} from '@akeneo-pim-community/shared';

const mockRouter: Router = {
  generate: jest.fn((route: string) => route),
  redirect: jest.fn(),
};

beforeEach(() => {
  jest.restoreAllMocks();
  (mockRouter.generate as jest.Mock).mockClear();
});

describe('moveCategory', () => {
  it('returns true when the response is ok', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce(new Response('', {status: 200}));
    const result = await moveCategory(mockRouter, {identifier: 5, parentId: 1, previousCategoryId: null});
    expect(result).toBe(true);
  });

  it('returns false when the response is not ok', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce(new Response('', {status: 500}));
    const result = await moveCategory(mockRouter, {identifier: 5, parentId: 1, previousCategoryId: null});
    expect(result).toBe(false);
  });

  it('returns false when fetch throws a network error', async () => {
    jest.spyOn(global, 'fetch').mockRejectedValueOnce(new Error('Network error'));
    const result = await moveCategory(mockRouter, {identifier: 5, parentId: 1, previousCategoryId: null});
    expect(result).toBe(false);
  });

  it('passes empty string for prev_sibling when previousCategoryId is null', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce(new Response('', {status: 200}));
    await moveCategory(mockRouter, {identifier: 5, parentId: 1, previousCategoryId: null});
    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enrich_categorytree_movenode', {
      id: '5',
      parent: '1',
      prev_sibling: '',
    });
  });

  it('passes stringified id for prev_sibling when previousCategoryId is set', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce(new Response('', {status: 200}));
    await moveCategory(mockRouter, {identifier: 5, parentId: 1, previousCategoryId: 3});
    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enrich_categorytree_movenode', {
      id: '5',
      parent: '1',
      prev_sibling: '3',
    });
  });
});

import {deleteCategory} from './deleteCategory';

const mockRouter = {
  generate: jest.fn((route: string, params: {id: number}) => `/api/${route}/${params.id}`),
  redirect: jest.fn(),
  redirectToRoute: jest.fn(),
};

describe('deleteCategory (legacy)', () => {
  beforeEach(() => {
    mockRouter.generate.mockClear();
  });

  it('generates the route with the category id', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({}),
    } as any);

    await deleteCategory(mockRouter, 42);

    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enrich_categorytree_remove', {id: 42});
  });

  it('sends a DELETE request with X-Requested-With header', async () => {
    const fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({}),
    } as any);

    await deleteCategory(mockRouter, 42);

    expect(fetchSpy).toHaveBeenCalledWith(
      '/api/pim_enrich_categorytree_remove/42',
      expect.objectContaining({
        method: 'DELETE',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      })
    );
  });

  it('returns {ok: true, errorMessage: ""} on a successful response', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({}),
    } as any);

    const result = await deleteCategory(mockRouter, 7);

    expect(result).toEqual({ok: true, errorMessage: ''});
  });

  it('returns {ok: false, errorMessage} when the server responds with an error', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      json: () => Promise.resolve({message: 'Category not found'}),
    } as any);

    const result = await deleteCategory(mockRouter, 99);

    expect(result).toEqual({ok: false, errorMessage: 'Category not found'});
  });
});

import {saveEditCategoryForm} from './saveEditCategoryForm';
import {Router} from '@akeneo-pim-community/shared';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  translate: (key: string) => key,
}));

const mockRouter: Router = {
  generate: jest.fn((route: string) => route),
  redirect: jest.fn(),
};

const baseCategory: any = {
  id: 42,
  isRoot: false,
  template_uuid: null,
  root: null,
  properties: {code: 'master', labels: {}},
  attributes: {},
  permissions: {view: [], edit: [], own: []},
};

const defaultOptions = {
  applyPermissionsOnChildren: false,
  populateResponseCategory: (c: any) => c,
};

beforeEach(() => jest.restoreAllMocks());

describe('saveEditCategoryForm', () => {
  it('returns success with populated category on 200 response', async () => {
    const responseCategory = {...baseCategory, properties: {code: 'master', labels: {en_US: 'Master'}}};
    jest
      .spyOn(global, 'fetch')
      .mockResolvedValueOnce(new Response(JSON.stringify({success: true, category: responseCategory}), {status: 200}));

    const result = await saveEditCategoryForm(mockRouter, baseCategory, defaultOptions);

    expect(result).toEqual({success: true, category: responseCategory});
  });

  it('calls populateResponseCategory with the response category', async () => {
    const responseCategory = {...baseCategory};
    const populated = {...baseCategory, properties: {code: 'populated', labels: {}}};
    const populateResponseCategory = jest.fn().mockReturnValue(populated);
    jest
      .spyOn(global, 'fetch')
      .mockResolvedValueOnce(new Response(JSON.stringify({success: true, category: responseCategory}), {status: 200}));

    const result = await saveEditCategoryForm(mockRouter, baseCategory, {
      ...defaultOptions,
      populateResponseCategory,
    });

    expect(populateResponseCategory).toHaveBeenCalledWith(responseCategory);
    expect(result).toEqual({success: true, category: populated});
  });

  it('injects applyPermissionsOnChildren into the POST payload', async () => {
    const fetchSpy = jest
      .spyOn(global, 'fetch')
      .mockResolvedValueOnce(new Response(JSON.stringify({success: true, category: baseCategory}), {status: 200}));

    await saveEditCategoryForm(mockRouter, baseCategory, {...defaultOptions, applyPermissionsOnChildren: true});

    const body = JSON.parse((fetchSpy.mock.calls[0][1] as RequestInit).body as string);
    expect(body.permissions.apply_on_children).toBe(true);
  });

  it('returns success: false with error on non-ok response with object body', async () => {
    const error = {code: 'invalid', message: 'Something went wrong'};
    jest
      .spyOn(global, 'fetch')
      .mockResolvedValueOnce(new Response(JSON.stringify({success: false, error}), {status: 422}));

    const result = await saveEditCategoryForm(mockRouter, baseCategory, defaultOptions);

    expect(result).toEqual({success: false, error});
  });

  it('returns first error when response body is an array', async () => {
    const error = {message: 'First error'};
    jest
      .spyOn(global, 'fetch')
      .mockResolvedValueOnce(new Response(JSON.stringify([{success: false, error}]), {status: 422}));

    const result = await saveEditCategoryForm(mockRouter, baseCategory, defaultOptions);

    expect(result).toEqual({success: false, error});
  });

  it('returns fallback translate error when JSON parse fails', async () => {
    jest
      .spyOn(global, 'fetch')
      .mockResolvedValueOnce(new Response('not-json', {status: 500}));

    const result = await saveEditCategoryForm(mockRouter, baseCategory, defaultOptions);

    expect(result).toEqual({success: false, error: {message: 'pim_enrich.entity.category.content.edit.fail'}});
  });

  it('calls router.generate with the category id', async () => {
    jest
      .spyOn(global, 'fetch')
      .mockResolvedValueOnce(new Response(JSON.stringify({success: true, category: baseCategory}), {status: 200}));

    await saveEditCategoryForm(mockRouter, baseCategory, defaultOptions);

    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enriched_category_rest_update', {id: 42});
  });
});

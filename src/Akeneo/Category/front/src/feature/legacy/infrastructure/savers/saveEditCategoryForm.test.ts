import {saveEditCategoryForm} from './saveEditCategoryForm';
import {Router} from '@akeneo-pim-community/shared';
import {EditCategoryForm} from '../../models';

const mockRouter: Router = {
  generate: jest.fn((route: string) => route),
  redirect: jest.fn(),
};

const baseForm: EditCategoryForm = {
  label: {
    en_US: {value: 'Master', fullName: 'category[labels][en_US]', label: 'English'},
  },
  _token: {value: 'tok123', fullName: 'category[_token]'},
  errors: [],
};

const mockCategory = {id: 1, code: 'master', labels: {}, root: null};
const mockResponseForm: EditCategoryForm = {...baseForm, errors: []};

beforeEach(() => {
  jest.restoreAllMocks();
  (mockRouter.generate as jest.Mock).mockClear();
});

describe('saveEditCategoryForm (legacy)', () => {
  it('returns success=true and the response form/category on ok response', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce(
      new Response(JSON.stringify({form: mockResponseForm, category: mockCategory}), {status: 200})
    );

    const result = await saveEditCategoryForm(mockRouter, 1, baseForm);

    expect(result.success).toBe(true);
    expect(result.category).toEqual(mockCategory);
    expect(result.form).toEqual(mockResponseForm);
  });

  it('returns success=false on non-ok response', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce(
      new Response(JSON.stringify({form: mockResponseForm, category: mockCategory}), {status: 422})
    );

    const result = await saveEditCategoryForm(mockRouter, 1, baseForm);

    expect(result.success).toBe(false);
  });

  it('includes the token and label fields in the URLSearchParams body', async () => {
    const fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValueOnce(
      new Response(JSON.stringify({form: mockResponseForm, category: mockCategory}), {status: 200})
    );

    await saveEditCategoryForm(mockRouter, 1, baseForm);

    const body = (fetchSpy.mock.calls[0][1] as RequestInit).body as URLSearchParams;
    expect(body.get('category[_token]')).toBe('tok123');
    expect(body.get('category[labels][en_US]')).toBe('Master');
  });

  it('includes all permission fields when permissions are present', async () => {
    const formWithPermissions: EditCategoryForm = {
      ...baseForm,
      permissions: {
        view: {value: ['1', '2'], fullName: 'category[permissions][view]', choices: []},
        edit: {value: ['1'], fullName: 'category[permissions][edit]', choices: []},
        own: {value: [], fullName: 'category[permissions][own]', choices: []},
        apply_on_children: {value: '1', fullName: 'category[permissions][apply_on_children]'},
      },
    };
    const fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValueOnce(
      new Response(JSON.stringify({form: mockResponseForm, category: mockCategory}), {status: 200})
    );

    await saveEditCategoryForm(mockRouter, 1, formWithPermissions);

    const body = (fetchSpy.mock.calls[0][1] as RequestInit).body as URLSearchParams;
    expect(body.getAll('category[permissions][view]')).toEqual(['1', '2']);
    expect(body.getAll('category[permissions][edit]')).toEqual(['1']);
    expect(body.get('category[permissions][apply_on_children]')).toBe('1');
  });

  it('omits apply_on_children when its value is not "1"', async () => {
    const formWithPermissions: EditCategoryForm = {
      ...baseForm,
      permissions: {
        view: {value: [], fullName: 'category[permissions][view]', choices: []},
        edit: {value: [], fullName: 'category[permissions][edit]', choices: []},
        own: {value: [], fullName: 'category[permissions][own]', choices: []},
        apply_on_children: {value: '0', fullName: 'category[permissions][apply_on_children]'},
      },
    };
    const fetchSpy = jest.spyOn(global, 'fetch').mockResolvedValueOnce(
      new Response(JSON.stringify({form: mockResponseForm, category: mockCategory}), {status: 200})
    );

    await saveEditCategoryForm(mockRouter, 1, formWithPermissions);

    const body = (fetchSpy.mock.calls[0][1] as RequestInit).body as URLSearchParams;
    expect(body.get('category[permissions][apply_on_children]')).toBeNull();
  });

  it('calls router.generate with the category id', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValueOnce(
      new Response(JSON.stringify({form: mockResponseForm, category: mockCategory}), {status: 200})
    );

    await saveEditCategoryForm(mockRouter, 42, baseForm);

    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enrich_categorytree_edit', {id: 42});
  });
});

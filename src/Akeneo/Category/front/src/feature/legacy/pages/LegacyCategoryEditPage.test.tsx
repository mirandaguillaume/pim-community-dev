import React from 'react';
import {screen} from '@testing-library/react';
import {MemoryRouter, Route, Routes} from 'react-router-dom';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {LegacyCategoryEditPage} from './LegacyCategoryEditPage';
import {useEditCategoryForm} from '../hooks/useEditCategoryForm';
import {useDeleteCategory} from '../hooks/useDeleteCategory';
import {useCountProductsBeforeDeleteCategory} from '../hooks/useCountProductsBeforeDeleteCategory';

jest.mock('../hooks/useEditCategoryForm');
jest.mock('../hooks/useDeleteCategory');
jest.mock('../hooks/useCountProductsBeforeDeleteCategory');
jest.mock('../../pages/HistoryPimView', () => ({HistoryPimView: () => null, View: undefined}));
jest.mock('../components/datagrids/DeleteCategoryModal', () => ({
  DeleteCategoryModal: () => null,
}));
jest.mock('../components', () => ({
  EditPermissionsForm: () => null,
  EditPropertiesForm: ({category}: any) => <div data-testid="edit-properties-form">{category?.code}</div>,
}));

const mockedUseEditForm = useEditCategoryForm as jest.MockedFunction<typeof useEditCategoryForm>;
const mockedUseDelete = useDeleteCategory as jest.MockedFunction<typeof useDeleteCategory>;
const mockedUseCount = useCountProductsBeforeDeleteCategory as jest.MockedFunction<
  typeof useCountProductsBeforeDeleteCategory
>;

const setupMocks = (overrides = {}) => {
  mockedUseEditForm.mockReturnValue({
    category: {id: 1, code: 'electronics', labels: {en_US: 'Electronics'}, root: null},
    categoryLoadingStatus: 'fetched',
    formData: {label: {}, _token: {value: 'tok', fullName: 'cat[token]'}, errors: []},
    onChangeCategoryLabel: jest.fn(),
    onChangePermissions: jest.fn(),
    onChangeApplyPermissionsOnChildren: jest.fn(),
    thereAreUnsavedChanges: false,
    saveCategory: jest.fn(),
    historyVersion: 0,
    ...overrides,
  } as any);
  mockedUseDelete.mockReturnValue({isCategoryDeletionPossible: jest.fn(() => true), handleDeleteCategory: jest.fn()} as any);
  mockedUseCount.mockReturnValue(jest.fn());
};

const renderPage = () =>
  renderWithProviders(
    <MemoryRouter initialEntries={['/categories/1/edit']}>
      <Routes>
        <Route path="/categories/:categoryId/edit" element={<LegacyCategoryEditPage />} />
      </Routes>
    </MemoryRouter>
  );

describe('LegacyCategoryEditPage', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders the settings breadcrumb step', () => {
    setupMocks();
    renderPage();
    expect(screen.getByText('pim_menu.tab.settings')).toBeInTheDocument();
  });

  it('renders the categories breadcrumb step', () => {
    setupMocks();
    renderPage();
    expect(screen.getByText('pim_enrich.entity.category.plural_label')).toBeInTheDocument();
  });

  it('renders the save button', () => {
    setupMocks();
    renderPage();
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();
  });

  it('renders the properties tab', () => {
    setupMocks();
    renderPage();
    expect(screen.getByText('pim_common.properties')).toBeInTheDocument();
  });

  it('renders a full screen error when category fetch fails', () => {
    setupMocks({category: null, categoryLoadingStatus: 'error'});
    renderPage();
    expect(screen.getByText('error.exception')).toBeInTheDocument();
  });
});

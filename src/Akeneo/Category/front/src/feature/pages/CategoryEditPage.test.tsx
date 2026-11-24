import React from 'react';
import {screen} from '@testing-library/react';
import {MemoryRouter, Route, Routes} from 'react-router-dom';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {CategoryEditPage} from './CategoryEditPage';
import {useEditCategoryForm} from '../hooks/useEditCategoryForm';
import {useDeleteCategory} from '../hooks/useDeleteCategory';
import {useCountProductsBeforeDeleteCategory} from '../hooks/useCountProductsBeforeDeleteCategory';
import {partialMock} from '../../tests/partialMock';

jest.mock('../hooks/useEditCategoryForm');
jest.mock('../hooks/useDeleteCategory');
jest.mock('../hooks/useCountProductsBeforeDeleteCategory');
jest.mock('../components', () => ({
  ...jest.requireActual('../components'),
  EditAttributesForm: () => null,
  EditPropertiesForm: () => <div data-testid="edit-properties-form" />,
  EditPermissionsForm: () => null,
  TemplateTitle: () => null,
  CategoryPageContent: ({children}: {children: React.ReactNode}) => <div>{children}</div>,
  NoTemplateAttribute: () => null,
}));
jest.mock('./HistoryPimView', () => ({HistoryPimView: () => null}));
jest.mock('../components/datagrids/DeleteCategoryModal', () => ({
  DeleteCategoryModal: () => null,
}));
jest.mock('../components/providers/EditCategoryProvider', () => ({
  EditCategoryContext: require('react').createContext({
    channels: {},
    channelsFetchFailed: false,
    locales: {},
    localesFetchFailed: false,
  }),
  EditCategoryProvider: ({children}: {children: React.ReactNode}) => children,
}));

const mockedUseEditForm = useEditCategoryForm as jest.MockedFunction<typeof useEditCategoryForm>;
const mockedUseDelete = useDeleteCategory as jest.MockedFunction<typeof useDeleteCategory>;
const mockedUseCount = useCountProductsBeforeDeleteCategory as jest.MockedFunction<
  typeof useCountProductsBeforeDeleteCategory
>;

const makeCategory = () => ({
  id: 1,
  isRoot: false,
  template_uuid: null,
  root: null,
  properties: {code: 'electronics', labels: {en_US: 'Electronics'}},
  attributes: {},
  permissions: {view: [], edit: [], own: []},
});

const setupMocks = (overrides = {}) => {
  mockedUseEditForm.mockReturnValue(
    partialMock<ReturnType<typeof useEditCategoryForm>>({
      category: makeCategory(),
      template: undefined,
      categoryFetchingStatus: 'fetched',
      applyPermissionsOnChildren: false,
      onChangeCategoryLabel: jest.fn(),
      onChangePermissions: jest.fn(),
      onChangeAttribute: jest.fn(),
      onChangeApplyPermissionsOnChildren: jest.fn(),
      isModified: false,
      saveCategory: jest.fn(),
      historyVersion: 0,
      ...overrides,
    })
  );
  mockedUseDelete.mockReturnValue(
    partialMock<ReturnType<typeof useDeleteCategory>>({
      isCategoryDeletionPossible: jest.fn(() => true),
      handleDeleteCategory: jest.fn(),
    })
  );
  mockedUseCount.mockReturnValue(jest.fn());
};

const renderPage = () =>
  renderWithProviders(
    <MemoryRouter initialEntries={['/categories/1/edit']}>
      <Routes>
        <Route path="/categories/:categoryId/edit" element={<CategoryEditPage />} />
      </Routes>
    </MemoryRouter>
  );

describe('CategoryEditPage', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders breadcrumb steps', () => {
    setupMocks();
    renderPage();
    expect(screen.getByText('pim_menu.tab.settings')).toBeInTheDocument();
    expect(screen.getByText('pim_enrich.entity.category.plural_label')).toBeInTheDocument();
  });

  it('renders the save button', () => {
    setupMocks();
    renderPage();
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();
  });

  it('renders a full screen error when category fetch fails', () => {
    setupMocks({category: null, categoryFetchingStatus: 'error'});
    renderPage();
    expect(screen.getByText('error.exception')).toBeInTheDocument();
  });

  it('renders the properties tab', () => {
    setupMocks();
    renderPage();
    expect(screen.getByText('pim_common.properties')).toBeInTheDocument();
  });
});

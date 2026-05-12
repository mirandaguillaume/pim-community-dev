import React from 'react';
import {screen} from '@testing-library/react';
import {MemoryRouter, Route, Routes} from 'react-router-dom';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {CategoriesTreePage} from './CategoriesTreePage';
import {useCategoryTreeDeprecated} from '../hooks/useCategoryTreeDeprecated';
import {useDeleteCategory} from '../hooks/useDeleteCategory';

jest.mock('../hooks/useCategoryTreeDeprecated');
jest.mock('../hooks/useDeleteCategory');
jest.mock('../components', () => ({
  ...jest.requireActual('../components'),
  CategoryTree: () => <div data-testid="category-tree" />,
  DiscoverEnrichedCategoriesInformationHelper: () => null,
}));
jest.mock('./NewCategoryModal', () => ({
  NewCategoryModal: () => <div data-testid="new-category-modal" />,
}));
jest.mock('../components/datagrids/DeleteCategoryModal', () => ({
  DeleteCategoryModal: () => <div data-testid="delete-category-modal" />,
}));

const mockedUseTree = useCategoryTreeDeprecated as jest.MockedFunction<typeof useCategoryTreeDeprecated>;
const mockedUseDelete = useDeleteCategory as jest.MockedFunction<typeof useDeleteCategory>;

const renderPage = () =>
  renderWithProviders(
    <MemoryRouter initialEntries={['/category-trees/1']}>
      <Routes>
        <Route path="/category-trees/:treeId" element={<CategoriesTreePage />} />
      </Routes>
    </MemoryRouter>
  );

describe('CategoriesTreePage', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    mockedUseDelete.mockReturnValue({isCategoryDeletionPossible: jest.fn(() => true), handleDeleteCategory: jest.fn()} as any);
  });

  it('renders the CategoryTree when tree is loaded', () => {
    mockedUseTree.mockReturnValue({
      tree: {id: 1, code: 'master', label: 'Master catalog', isRoot: true, isLeaf: false},
      loadingStatus: 'fetched',
      loadTree: jest.fn(),
    } as any);
    renderPage();
    expect(screen.getByTestId('category-tree')).toBeInTheDocument();
  });

  it('renders the settings breadcrumb step', () => {
    mockedUseTree.mockReturnValue({tree: null, loadingStatus: 'fetched', loadTree: jest.fn()} as any);
    renderPage();
    expect(screen.getByText('pim_menu.tab.settings')).toBeInTheDocument();
  });

  it('renders the category plural label breadcrumb step', () => {
    mockedUseTree.mockReturnValue({tree: null, loadingStatus: 'fetched', loadTree: jest.fn()} as any);
    renderPage();
    const elements = screen.getAllByText('pim_enrich.entity.category.plural_label');
    expect(elements.length).toBeGreaterThan(0);
  });

  it('renders a full screen error when loading fails', () => {
    mockedUseTree.mockReturnValue({tree: null, loadingStatus: 'error', loadTree: jest.fn()} as any);
    renderPage();
    expect(screen.getByText('error.exception')).toBeInTheDocument();
  });
});

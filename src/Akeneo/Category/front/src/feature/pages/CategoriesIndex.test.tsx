import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {CategoriesIndex} from './CategoriesIndex';
import {useCategoryTreeList} from '../hooks/useCategoryTreeList';

jest.mock('../hooks/useCategoryTreeList');
jest.mock('./NewCategoryModal', () => ({
  NewCategoryModal: () => <div data-testid="new-category-modal" />,
}));
jest.mock('../components', () => ({
  ...jest.requireActual('../components'),
  CategoryTreesDataGrid: () => <div data-testid="category-trees-datagrid" />,
  DiscoverEnrichedCategoriesInformationHelper: () => null,
  EmptyCategoryTreeList: () => <div data-testid="empty-category-tree-list" />,
}));

const mockedUseList = useCategoryTreeList as jest.MockedFunction<typeof useCategoryTreeList>;

describe('CategoriesIndex', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders the settings breadcrumb step', () => {
    mockedUseList.mockReturnValue({trees: [], loadingStatus: 'fetched', loadTrees: jest.fn()} as any);
    renderWithProviders(<CategoriesIndex />);
    expect(screen.getByText('pim_menu.tab.settings')).toBeInTheDocument();
  });

  it('renders the categories breadcrumb step', () => {
    mockedUseList.mockReturnValue({trees: [], loadingStatus: 'fetched', loadTrees: jest.fn()} as any);
    renderWithProviders(<CategoriesIndex />);
    expect(screen.getByText('pim_enrich.entity.category.plural_label')).toBeInTheDocument();
  });

  it('renders EmptyCategoryTreeList when there are no trees', () => {
    mockedUseList.mockReturnValue({trees: [], loadingStatus: 'fetched', loadTrees: jest.fn()} as any);
    renderWithProviders(<CategoriesIndex />);
    expect(screen.getByTestId('empty-category-tree-list')).toBeInTheDocument();
  });

  it('renders CategoryTreesDataGrid when there are trees', () => {
    const trees = [{id: 1, code: 'master', label: 'Master', isRoot: true, isLeaf: false}];
    mockedUseList.mockReturnValue({trees, loadingStatus: 'fetched', loadTrees: jest.fn()} as any);
    renderWithProviders(<CategoriesIndex />);
    expect(screen.getByTestId('category-trees-datagrid')).toBeInTheDocument();
  });
});

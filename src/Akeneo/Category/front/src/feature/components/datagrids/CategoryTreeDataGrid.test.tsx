import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {CategoryTreesDataGrid} from './CategoryTreeDataGrid';
import {useCountCategoryTreesChildren} from '../../hooks/useCountCategoryTreesChildren';

jest.mock('../../hooks/useCountCategoryTreesChildren');
jest.mock('../templates/CreateTemplateModal', () => ({
  CreateTemplateModal: () => <div data-testid="create-template-modal" />,
}));
jest.mock('./DeleteCategoryModal', () => ({
  DeleteCategoryModal: () => <div data-testid="delete-modal" />,
}));
jest.mock('../../infrastructure', () => ({
  ...jest.requireActual('../../infrastructure'),
  deleteCategory: jest.fn().mockResolvedValue({ok: true}),
}));
jest.mock('../../../tools/useDebounceCallback', () => ({
  useDebounceCallback: jest.fn((callback: (...args: any[]) => void) => callback),
}));

const mockedUseCount = useCountCategoryTreesChildren as jest.MockedFunction<typeof useCountCategoryTreesChildren>;

const trees = [
  {id: 1, code: 'master', label: 'Master catalog', isRoot: true, isLeaf: false, productsNumber: 5},
  {id: 2, code: 'seasonal', label: 'Seasonal', isRoot: true, isLeaf: false, productsNumber: 2},
];

const renderDataGrid = (props = {}) => {
  mockedUseCount.mockReturnValue({'master': 12, 'seasonal': 3});
  return renderWithProviders(
    <CategoryTreesDataGrid trees={trees} refreshCategoryTrees={jest.fn()} {...props} />
  );
};

describe('CategoryTreesDataGrid', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders the search input', () => {
    renderDataGrid();
    expect(screen.getByPlaceholderText('pim_common.search')).toBeInTheDocument();
  });

  it('renders a row for each tree', () => {
    renderDataGrid();
    expect(screen.getByText('Master catalog')).toBeInTheDocument();
    expect(screen.getByText('Seasonal')).toBeInTheDocument();
  });

  it('filters trees when searching by label', async () => {
    renderDataGrid();
    const searchInput = screen.getByPlaceholderText('pim_common.search');
    await userEvent.type(searchInput, 'master');
    expect(screen.getByText('Master catalog')).toBeInTheDocument();
  });

  it('shows NoResults when search has no matches', async () => {
    renderDataGrid();
    const searchInput = screen.getByPlaceholderText('pim_common.search');
    await userEvent.type(searchInput, 'zzz_no_match');
    expect(screen.getByText('pim_datagrid.no_results')).toBeInTheDocument();
  });

  it('renders result count', () => {
    renderDataGrid();
    expect(screen.getByText('pim_common.result_count')).toBeInTheDocument();
  });
});

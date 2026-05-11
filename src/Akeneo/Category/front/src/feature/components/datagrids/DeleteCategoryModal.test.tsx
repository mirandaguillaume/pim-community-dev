import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {DeleteCategoryModal} from './DeleteCategoryModal';
import {useCountCategoryChildren} from '../../hooks/useCountCategoryChildren';

jest.mock('../../hooks/useCountCategoryChildren');
const mockedUseCount = useCountCategoryChildren as jest.MockedFunction<typeof useCountCategoryChildren>;

const defaultProps = {
  categoryLabel: 'Summer',
  closeModal: jest.fn(),
  deleteCategory: jest.fn(),
  message: 'pim_enrich.entity.category.category_tree_deletion.message',
  categoryId: 42,
};

describe('DeleteCategoryModal', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('shows a spinner state while loading', () => {
    mockedUseCount.mockReturnValue({data: undefined, isLoading: true});
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
    expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  });

  it('shows no warning when category has no children and no products', () => {
    mockedUseCount.mockReturnValue({data: 0, isLoading: false});
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    expect(screen.queryByRole('alert')).not.toBeInTheDocument();
  });

  it('shows a children-count warning when category has children', () => {
    mockedUseCount.mockReturnValue({data: 3, isLoading: false});
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    expect(
      screen.getByText('pim_enrich.entity.category.category_tree_deletion.warning_categories_number')
    ).toBeInTheDocument();
  });

  it('shows a products warning when category has no children but has products', () => {
    mockedUseCount.mockReturnValue({data: 0, isLoading: false});
    renderWithProviders(<DeleteCategoryModal {...defaultProps} numberOfProducts={5} />);
    expect(
      screen.getByText('pim_enrich.entity.category.category_tree_deletion.warning_products')
    ).toBeInTheDocument();
  });

  it('calls closeModal when the cancel button is clicked', async () => {
    mockedUseCount.mockReturnValue({data: 0, isLoading: false});
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    await userEvent.click(screen.getByText('pim_common.cancel'));
    expect(defaultProps.closeModal).toHaveBeenCalledTimes(1);
  });

  it('calls deleteCategory when the delete button is clicked', async () => {
    mockedUseCount.mockReturnValue({data: 0, isLoading: false});
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    await userEvent.click(screen.getByText('pim_common.delete'));
    expect(defaultProps.deleteCategory).toHaveBeenCalledTimes(1);
  });
});

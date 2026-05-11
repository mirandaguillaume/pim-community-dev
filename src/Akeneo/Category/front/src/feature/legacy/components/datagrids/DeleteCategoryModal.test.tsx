import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {DeleteCategoryModal} from './DeleteCategoryModal';

const defaultProps = {
  categoryLabel: 'Electronics',
  closeModal: jest.fn(),
  deleteCategory: jest.fn(),
  message: 'pim_enrich.entity.category.category_tree_deletion.message',
};

describe('DeleteCategoryModal (legacy)', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders the confirm deletion title', () => {
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
  });

  it('renders the plural label section title', () => {
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    expect(screen.getByText('pim_enrich.entity.category.plural_label')).toBeInTheDocument();
  });

  it('renders the translated message', () => {
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    expect(
      screen.getByText('pim_enrich.entity.category.category_tree_deletion.message')
    ).toBeInTheDocument();
  });

  it('calls closeModal when the cancel button is clicked', async () => {
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    await userEvent.click(screen.getByText('pim_common.cancel'));
    expect(defaultProps.closeModal).toHaveBeenCalledTimes(1);
  });

  it('calls deleteCategory when the delete button is clicked', async () => {
    renderWithProviders(<DeleteCategoryModal {...defaultProps} />);
    await userEvent.click(screen.getByText('pim_common.delete'));
    expect(defaultProps.deleteCategory).toHaveBeenCalledTimes(1);
  });
});

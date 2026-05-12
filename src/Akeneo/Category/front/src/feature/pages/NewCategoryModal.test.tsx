import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {NewCategoryModal} from './NewCategoryModal';
import {createCategory} from '../infrastructure';

jest.mock('../infrastructure', () => ({
  ...jest.requireActual('../infrastructure'),
  createCategory: jest.fn().mockResolvedValue({}),
}));
const mockedCreateCategory = createCategory as jest.MockedFunction<typeof createCategory>;

const defaultProps = {
  closeModal: jest.fn(),
  onCreate: jest.fn(),
};

describe('NewCategoryModal', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders the code input field', () => {
    renderWithProviders(<NewCategoryModal {...defaultProps} />);
    expect(screen.getByText('pim_common.code')).toBeInTheDocument();
  });

  it('renders the new category tree title when no parentCode is provided', () => {
    renderWithProviders(<NewCategoryModal {...defaultProps} />);
    expect(screen.getByText('pim_enrich.entity.category.new_category_tree')).toBeInTheDocument();
  });

  it('renders the new category title when parentCode is provided', () => {
    renderWithProviders(<NewCategoryModal {...defaultProps} parentCode="master" />);
    expect(screen.getByText('pim_enrich.entity.category.new_category')).toBeInTheDocument();
  });

  it('disables the create button when code is empty', () => {
    renderWithProviders(<NewCategoryModal {...defaultProps} />);
    expect(screen.getByText('pim_common.create').closest('button')).toBeDisabled();
  });

  it('enables the create button when code is typed', async () => {
    renderWithProviders(<NewCategoryModal {...defaultProps} />);
    const inputs = screen.getAllByRole('textbox');
    await userEvent.type(inputs[0], 'electronics');
    expect(screen.getByText('pim_common.create').closest('button')).not.toBeDisabled();
  });

  it('calls closeModal when the close button is clicked', async () => {
    renderWithProviders(<NewCategoryModal {...defaultProps} />);
    await userEvent.click(screen.getByTitle('Close'));
    expect(defaultProps.closeModal).toHaveBeenCalledTimes(1);
  });

  it('calls createCategory when the create button is clicked with a code', async () => {
    renderWithProviders(<NewCategoryModal {...defaultProps} />);
    const inputs = screen.getAllByRole('textbox');
    await userEvent.type(inputs[0], 'electronics');
    await userEvent.click(screen.getByText('pim_common.create'));
    expect(mockedCreateCategory).toHaveBeenCalledWith(
      expect.any(Object),
      'electronics',
      undefined,
      expect.any(String),
      ''
    );
  });

  it('shows validation errors when createCategory returns errors', async () => {
    mockedCreateCategory.mockResolvedValue({code: 'Code already taken'});
    renderWithProviders(<NewCategoryModal {...defaultProps} />);
    const inputs = screen.getAllByRole('textbox');
    await userEvent.type(inputs[0], 'bad_code');
    await userEvent.click(screen.getByText('pim_common.create'));
    expect(await screen.findByText('Code already taken')).toBeInTheDocument();
  });
});

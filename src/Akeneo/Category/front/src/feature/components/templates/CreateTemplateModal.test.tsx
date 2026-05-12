import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {CreateTemplateModal} from './CreateTemplateModal';
import {useCreateTemplate} from '../../hooks/useCreateTemplate';

jest.mock('../../hooks/useCreateTemplate');
const mockedUseCreateTemplate = useCreateTemplate as jest.MockedFunction<typeof useCreateTemplate>;

const categoryTree = {id: 1, label: 'Master', code: 'master', isRoot: true};

describe('CreateTemplateModal', () => {
  let mockMutate: jest.Mock;
  let mockOnClose: jest.Mock;

  beforeEach(() => {
    mockMutate = jest.fn();
    mockOnClose = jest.fn();
    mockedUseCreateTemplate.mockReturnValue({mutate: mockMutate, isPending: false, error: null} as any);
  });

  it('renders the create template section title', () => {
    renderWithProviders(<CreateTemplateModal categoryTree={categoryTree} onClose={mockOnClose} />);
    expect(screen.getByText('akeneo.category.template.create')).toBeInTheDocument();
  });

  it('renders the category tree label', () => {
    renderWithProviders(<CreateTemplateModal categoryTree={categoryTree} onClose={mockOnClose} />);
    expect(screen.getByText('Master')).toBeInTheDocument();
  });

  it('renders the label and code fields', () => {
    renderWithProviders(<CreateTemplateModal categoryTree={categoryTree} onClose={mockOnClose} />);
    expect(screen.getByText('pim_common.label')).toBeInTheDocument();
    expect(screen.getByText('pim_common.code')).toBeInTheDocument();
  });

  it('calls onClose when the cancel button is clicked', async () => {
    renderWithProviders(<CreateTemplateModal categoryTree={categoryTree} onClose={mockOnClose} />);
    await userEvent.click(screen.getByText('pim_common.cancel'));
    expect(mockOnClose).toHaveBeenCalledTimes(1);
  });

  it('calls mutate with form values when the create button is clicked', async () => {
    renderWithProviders(<CreateTemplateModal categoryTree={categoryTree} onClose={mockOnClose} />);
    const inputs = screen.getAllByRole('textbox');
    await userEvent.type(inputs[0], 'My Template');
    await userEvent.type(inputs[1], 'my_template');
    await userEvent.click(
      screen.getByText('akeneo.category.template.add_attribute.confirmation_modal.create')
    );
    expect(mockMutate).toHaveBeenCalledWith(
      expect.objectContaining({
        categoryTreeId: 1,
        label: 'My Template',
        code: 'my_template',
      }),
      expect.any(Object)
    );
  });

  it('disables the create button while pending', () => {
    mockedUseCreateTemplate.mockReturnValue({mutate: mockMutate, isPending: true, error: null} as any);
    renderWithProviders(<CreateTemplateModal categoryTree={categoryTree} onClose={mockOnClose} />);
    expect(
      screen.getByText('akeneo.category.template.add_attribute.confirmation_modal.create').closest('button')
    ).toBeDisabled();
  });
});

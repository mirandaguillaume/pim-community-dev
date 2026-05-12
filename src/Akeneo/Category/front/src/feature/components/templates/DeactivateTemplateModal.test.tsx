import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {DeactivateTemplateModal} from './DeactivateTemplateModal';
import {useDeactivateTemplate} from '../../hooks/useDeactivateTemplate';

jest.mock('../../hooks/useDeactivateTemplate');
const mockedUseDeactivate = useDeactivateTemplate as jest.MockedFunction<typeof useDeactivateTemplate>;

const template = {id: 'tmpl-uuid', label: 'T-Shirts'};

describe('DeactivateTemplateModal', () => {
  let mockDeactivate: jest.Mock;
  let mockOnClose: jest.Mock;

  beforeEach(() => {
    mockDeactivate = jest.fn();
    mockOnClose = jest.fn();
    mockedUseDeactivate.mockReturnValue(mockDeactivate);
  });

  it('renders the deactivate template section title', () => {
    renderWithProviders(<DeactivateTemplateModal template={template} onClose={mockOnClose} />);
    expect(
      screen.getByText('akeneo.category.template.deactivate.deactivate_template')
    ).toBeInTheDocument();
  });

  it('renders the confirmation modal title', () => {
    renderWithProviders(<DeactivateTemplateModal template={template} onClose={mockOnClose} />);
    expect(
      screen.getByText('akeneo.category.template.deactivate.confirmation_modal.title')
    ).toBeInTheDocument();
  });

  it('renders the helper warning', () => {
    renderWithProviders(<DeactivateTemplateModal template={template} onClose={mockOnClose} />);
    expect(
      screen.getByText('akeneo.category.template.deactivate.confirmation_modal.helper')
    ).toBeInTheDocument();
  });

  it('calls onClose when the cancel button is clicked', async () => {
    renderWithProviders(<DeactivateTemplateModal template={template} onClose={mockOnClose} />);
    await userEvent.click(screen.getByText('pim_common.cancel'));
    expect(mockOnClose).toHaveBeenCalledTimes(1);
  });

  it('calls deactivateTemplate when the deactivate button is clicked', async () => {
    renderWithProviders(<DeactivateTemplateModal template={template} onClose={mockOnClose} />);
    await userEvent.click(
      screen.getByText('akeneo.category.template.deactivate.confirmation_modal.deactivate')
    );
    expect(mockDeactivate).toHaveBeenCalledTimes(1);
  });
});

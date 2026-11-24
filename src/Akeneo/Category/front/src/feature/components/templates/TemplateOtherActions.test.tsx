import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {TemplateOtherActions} from './TemplateOtherActions';

describe('TemplateOtherActions', () => {
  it('renders the "other actions" icon button', () => {
    renderWithProviders(<TemplateOtherActions onDeactivateTemplate={jest.fn()} />);
    expect(screen.getByTitle('akeneo.category.other_actions')).toBeInTheDocument();
  });

  it('does not show the dropdown overlay initially', () => {
    renderWithProviders(<TemplateOtherActions onDeactivateTemplate={jest.fn()} />);
    expect(screen.queryByText('akeneo.category.template.deactivate.deactivate_template')).not.toBeInTheDocument();
  });

  it('reveals the deactivate option when the button is clicked', () => {
    renderWithProviders(<TemplateOtherActions onDeactivateTemplate={jest.fn()} />);

    userEvent.click(screen.getByTitle('akeneo.category.other_actions'));

    expect(screen.getByText('akeneo.category.template.deactivate.deactivate_template')).toBeInTheDocument();
  });

  it('calls onDeactivateTemplate and closes the dropdown when the deactivate item is clicked', () => {
    const onDeactivateTemplate = jest.fn();
    renderWithProviders(<TemplateOtherActions onDeactivateTemplate={onDeactivateTemplate} />);

    userEvent.click(screen.getByTitle('akeneo.category.other_actions'));
    userEvent.click(screen.getByText('akeneo.category.template.deactivate.deactivate_template'));

    expect(onDeactivateTemplate).toHaveBeenCalledTimes(1);
    expect(screen.queryByText('akeneo.category.template.deactivate.deactivate_template')).not.toBeInTheDocument();
  });
});

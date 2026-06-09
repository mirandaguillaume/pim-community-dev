import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ChooseApp} from '../../../../../Resources/public/js/mass-edit/form/ChooseApp';

const operations = [
  {code: 'edit_common', label: 'Edit attributes', icon: 'icon-edit'},
  {code: 'add_to_group', label: 'Add to group', icon: 'icon-groups'},
];

test('It renders all operation labels', () => {
  renderWithProviders(<ChooseApp operations={operations} onChange={jest.fn()} />);
  expect(screen.getByText('Edit attributes')).toBeInTheDocument();
  expect(screen.getByText('Add to group')).toBeInTheDocument();
});

test('It calls onChange with the clicked operation code', () => {
  const onChange = jest.fn();
  renderWithProviders(<ChooseApp operations={operations} onChange={onChange} />);
  userEvent.click(screen.getByText('Edit attributes'));
  expect(onChange).toHaveBeenCalledWith('edit_common');
});

test('It renders with a pre-selected operation', () => {
  renderWithProviders(<ChooseApp operations={operations} selectedOperationCode="add_to_group" onChange={jest.fn()} />);
  expect(screen.getByText('Add to group')).toBeInTheDocument();
});

test('It renders without crash for an unknown icon', () => {
  const unknownOps = [{code: 'unknown', label: 'Unknown op', icon: 'icon-nonexistent'}];
  renderWithProviders(<ChooseApp operations={unknownOps} onChange={jest.fn()} />);
  expect(screen.getByText('Unknown op')).toBeInTheDocument();
});

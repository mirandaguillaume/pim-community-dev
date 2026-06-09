import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {FamilyTemplateSelector} from '../../../../../../Resources/public/js/family/form/template/FamilyTemplateSelector';

test('It renders the modal close button', () => {
  renderWithProviders(<FamilyTemplateSelector close={jest.fn()} />);
  expect(screen.getByTitle('pim_common.cancel')).toBeInTheDocument();
});

test('It calls close when the close button is clicked', () => {
  const close = jest.fn();
  renderWithProviders(<FamilyTemplateSelector close={close} />);
  userEvent.click(screen.getByTitle('pim_common.cancel'));
  expect(close).toHaveBeenCalledTimes(1);
});

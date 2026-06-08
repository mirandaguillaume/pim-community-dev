import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AddItem} from '../../../../../../../front/src/application/component/Dashboard/Widgets/AddItem';

test('it renders the children label', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AddItem add={jest.fn()}>Add families</AddItem>
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('Add families')).toBeInTheDocument();
});

test('it calls the add callback when clicked', async () => {
  const handleAdd = jest.fn();
  const user = userEvent.setup();

  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AddItem add={handleAdd}>Add families</AddItem>
      </ThemeProvider>
    </DependenciesProvider>
  );

  await user.click(screen.getByRole('button', {name: 'Add families'}));
  expect(handleAdd).toHaveBeenCalledTimes(1);
});

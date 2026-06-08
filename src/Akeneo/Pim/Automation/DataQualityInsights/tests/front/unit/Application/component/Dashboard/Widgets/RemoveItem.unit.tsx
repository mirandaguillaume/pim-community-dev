import React from 'react';
import {render} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {RemoveItem} from '../../../../../../../front/src/application/component/Dashboard/Widgets/RemoveItem';

test('it calls the remove callback when the close icon is clicked', async () => {
  const handleRemove = jest.fn();
  const user = userEvent.setup();

  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <RemoveItem remove={handleRemove} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  const svg = document.querySelector('svg') as SVGElement;
  await user.click(svg);
  expect(handleRemove).toHaveBeenCalledTimes(1);
});

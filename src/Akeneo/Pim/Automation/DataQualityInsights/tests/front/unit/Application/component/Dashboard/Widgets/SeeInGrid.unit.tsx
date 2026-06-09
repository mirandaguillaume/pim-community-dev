import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {SeeInGrid} from '../../../../../../../front/src/application/component/Dashboard/Widgets/SeeInGrid';

test('it renders the translated label', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <SeeInGrid follow={jest.fn()} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('akeneo_data_quality_insights.dqi_dashboard.widgets.see_in_grid')).toBeInTheDocument();
});

test('it calls the follow callback when clicked', () => {
  const handleFollow = jest.fn();

  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <SeeInGrid follow={handleFollow} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  userEvent.click(screen.getByRole('button', {name: 'akeneo_data_quality_insights.dqi_dashboard.widgets.see_in_grid'}));
  expect(handleFollow).toHaveBeenCalledTimes(1);
});

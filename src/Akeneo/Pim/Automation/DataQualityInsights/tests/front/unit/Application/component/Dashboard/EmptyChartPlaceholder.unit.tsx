import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {EmptyChartPlaceholder} from '../../../../../../../front/src/application/component/Dashboard/EmptyChartPlaceholder';

test('it renders both placeholder i18n messages', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <EmptyChartPlaceholder />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('akeneo_data_quality_insights.dqi_dashboard.no_data_title')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.dqi_dashboard.no_data_subtitle')).toBeInTheDocument();
});

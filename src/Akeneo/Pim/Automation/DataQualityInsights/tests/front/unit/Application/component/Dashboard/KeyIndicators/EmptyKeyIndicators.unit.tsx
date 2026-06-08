import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {EmptyKeyIndicators} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/EmptyKeyIndicators';

test('it renders both empty-state i18n messages', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <EmptyKeyIndicators />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.no_data')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.no_data_subtitle')
  ).toBeInTheDocument();
});

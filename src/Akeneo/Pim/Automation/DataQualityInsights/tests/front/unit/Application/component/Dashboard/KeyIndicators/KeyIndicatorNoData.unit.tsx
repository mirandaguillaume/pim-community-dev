import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {KeyIndicatorNoData} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/KeyIndicatorNoData';

test('it renders the no-data message for a given indicator type', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <KeyIndicatorNoData
          type="has_image"
          title="akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title"
        >
          <span data-testid="icon">icon</span>
        </KeyIndicatorNoData>
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.no_data')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title')
  ).toBeInTheDocument();
  expect(screen.getByTestId('icon')).toBeInTheDocument();
});

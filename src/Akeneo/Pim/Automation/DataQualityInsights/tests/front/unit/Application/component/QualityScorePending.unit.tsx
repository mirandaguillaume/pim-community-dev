import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {QualityScorePending} from '../../../../../front/src/application/component/QualityScorePending';

test('it renders the pending badge with its i18n key', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScorePending />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByTestId('quality-score-pending')).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.quality_score.pending')).toBeInTheDocument();
});

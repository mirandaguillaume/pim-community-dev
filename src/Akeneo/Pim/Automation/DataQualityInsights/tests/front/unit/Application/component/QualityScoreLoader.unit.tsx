import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {QualityScoreLoader} from '../../../../../front/src/application/component/QualityScoreLoader';

test('it renders the quality score loader skeleton', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <QualityScoreLoader />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByTestId('quality-score-loader')).toBeInTheDocument();
});

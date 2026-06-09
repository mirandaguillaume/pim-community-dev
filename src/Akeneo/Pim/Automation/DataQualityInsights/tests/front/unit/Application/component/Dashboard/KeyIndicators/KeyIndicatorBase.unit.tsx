import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {KeyIndicatorBase} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/KeyIndicatorBase';

const renderKeyIndicatorBase = (percentOK: number) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <KeyIndicatorBase
          percentOK={percentOK}
          titleI18nKey="akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title"
          icon={<span data-testid="test-icon">icon</span>}
        />
      </ThemeProvider>
    </DependenciesProvider>
  );

test('it renders the translated title', () => {
  renderKeyIndicatorBase(75);

  expect(
    screen.getByText('akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title')
  ).toBeInTheDocument();
});

test('it renders the percentage label', () => {
  renderKeyIndicatorBase(42);

  expect(screen.getByText('42%')).toBeInTheDocument();
});

test('it renders the icon slot', () => {
  renderKeyIndicatorBase(75);

  expect(screen.getByTestId('test-icon')).toBeInTheDocument();
});

test('it renders children when provided', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <KeyIndicatorBase percentOK={50} titleI18nKey="my_title" icon={<span>icon</span>}>
          <span data-testid="child-content">link</span>
        </KeyIndicatorBase>
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByTestId('child-content')).toBeInTheDocument();
});

import React from 'react';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AxisGradingInProgress} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisGradingInProgress';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('AxisGradingInProgress', () => {
  it('renders the grading in progress i18n key', () => {
    renderWith(<AxisGradingInProgress />);
    expect(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.messages.axis_grading_in_progress')
    ).toBeInTheDocument();
  });
});

import React from 'react';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AxisError} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisError';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('AxisError', () => {
  it('renders the axis error i18n key', () => {
    renderWith(<AxisError />);
    expect(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.messages.error.axis_error')
    ).toBeInTheDocument();
  });
});

import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Title} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion/Title';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('Title', () => {
  it('renders the translated criterion key when criterion is provided', () => {
    renderWith(<Title criterion="completeness_of_required_attributes" />);
    expect(
      screen.getByText(
        'akeneo_data_quality_insights.product_evaluation.criteria.completeness_of_required_attributes.recommendation:',
        {exact: false}
      )
    ).toBeInTheDocument();
  });

  it('renders the span container when no criterion is given', () => {
    const {container} = renderWith(<Title />);
    expect(container.querySelector('.CriterionRecommendationMessage')).toBeInTheDocument();
  });
});

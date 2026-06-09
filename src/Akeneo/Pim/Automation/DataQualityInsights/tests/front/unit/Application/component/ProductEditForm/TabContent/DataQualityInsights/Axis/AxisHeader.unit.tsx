import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});
jest.mock(
  '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AllAttributesLink',
  () => ({
    __esModule: true,
    default: ({attributes}: {attributes: string[]}) => (
      <span data-testid="all-attributes-link">link:{attributes.length}</span>
    ),
  })
);

import {AxisHeader} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Axis/AxisHeader';

const makeEvaluation = (improvableAttributes: string[] = []) => ({
  rate: null,
  criteria:
    improvableAttributes.length > 0
      ? [
          {
            code: 'criterion_1',
            rate: {value: 80, rank: 'B'},
            status: 'done' as const,
            improvable_attributes: improvableAttributes,
          },
        ]
      : [],
});

describe('AxisHeader', () => {
  it('renders the translated axis title', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AxisHeader evaluation={makeEvaluation()} axis="enrichment" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(
      screen.getByText('akeneo_data_quality_insights.product_evaluation.axis.enrichment.title')
    ).toBeInTheDocument();
  });

  it('does not render AllAttributesLink when no improvable attributes', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AxisHeader evaluation={makeEvaluation([])} axis="enrichment" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.queryByTestId('all-attributes-link')).not.toBeInTheDocument();
  });

  it('renders AllAttributesLink with attributes when improvable attributes exist', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AxisHeader evaluation={makeEvaluation(['attr_1', 'attr_2'])} axis="enrichment" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.getByTestId('all-attributes-link')).toBeInTheDocument();
    expect(screen.getByTestId('all-attributes-link').textContent).toBe('link:2');
  });
});

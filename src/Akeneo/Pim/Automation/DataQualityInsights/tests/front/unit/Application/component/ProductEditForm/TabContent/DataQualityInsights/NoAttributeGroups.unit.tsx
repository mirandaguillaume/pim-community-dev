import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

import {NoAttributeGroups} from '../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/NoAttributeGroups';

test('it renders the title, subtitle and help center link', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <NoAttributeGroups />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(
    screen.getByText('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.title')
  ).toBeInTheDocument();
  expect(
    screen.getByText('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.subtitle')
  ).toBeInTheDocument();
  expect(
    screen.getByRole('link', {
      name: 'akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.help_center_link',
    })
  ).toHaveAttribute('target', '_blank');
});

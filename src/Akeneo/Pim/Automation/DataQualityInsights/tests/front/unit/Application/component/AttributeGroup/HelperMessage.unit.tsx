import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

import {HelperMessage} from '../../../../../../front/src/application/component/AttributeGroup/HelperMessage';

test('it renders the helper info text and link', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <HelperMessage />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(
    screen.getByText('akeneo_data_quality_insights.attribute_group.helper_dqi_info', {exact: false})
  ).toBeInTheDocument();
  expect(screen.getByText('akeneo_data_quality_insights.attribute_group.helper_dqi_link')).toBeInTheDocument();
  expect(
    screen.getByRole('link', {name: 'akeneo_data_quality_insights.attribute_group.helper_dqi_link'})
  ).toHaveAttribute('target', '_blank');
});

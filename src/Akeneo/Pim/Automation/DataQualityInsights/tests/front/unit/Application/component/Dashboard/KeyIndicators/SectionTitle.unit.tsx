import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {SectionTitle} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/SectionTitle';

test('it renders the translated section title', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <SectionTitle title="my_i18n_key" />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('my_i18n_key')).toBeInTheDocument();
});

test('it renders children alongside the title', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <SectionTitle title="my_i18n_key">
          <button>action</button>
        </SectionTitle>
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByRole('button', {name: 'action'})).toBeInTheDocument();
});

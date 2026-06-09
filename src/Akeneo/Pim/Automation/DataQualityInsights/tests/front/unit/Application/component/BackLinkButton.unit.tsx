import React from 'react';
import {render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('pim/router', () => ({redirectToRoute: jest.fn()}), {virtual: true});

import {BackLinkButton} from '../../../../../front/src/application/component/BackLinkButton';

describe('BackLinkButton', () => {
  beforeEach(() => {
    const Router = require('pim/router');
    Router.redirectToRoute.mockClear();
  });

  it('renders with the given label', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <BackLinkButton label="Go back" route="pim_enrich_product_index" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.getByRole('button', {name: 'Go back'})).toBeInTheDocument();
  });

  it('calls Router.redirectToRoute with the correct route on click', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <BackLinkButton label="Go back" route="pim_enrich_product_index" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    userEvent.click(screen.getByRole('button', {name: 'Go back'}));

    const Router = require('pim/router');
    expect(Router.redirectToRoute).toHaveBeenCalledWith('pim_enrich_product_index', undefined);
  });

  it('passes routeParams to Router.redirectToRoute', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <BackLinkButton label="Go back" route="pim_enrich_product_index" routeParams={[]} />
        </ThemeProvider>
      </DependenciesProvider>
    );

    userEvent.click(screen.getByRole('button', {name: 'Go back'}));

    const Router = require('pim/router');
    expect(Router.redirectToRoute).toHaveBeenCalledWith('pim_enrich_product_index', []);
  });
});

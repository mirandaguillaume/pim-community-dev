import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {messageBuilder} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/messageBuilder';

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('messageBuilder', () => {
  it('replaces a known marker with the mapped JSX element', () => {
    const mapping = {
      '<count_link/>': <button>42 products</button>,
    };
    renderWith(messageBuilder(mapping)('You have <count_link/> to fix'));
    expect(screen.getByRole('button', {name: '42 products'})).toBeInTheDocument();
  });

  it('wraps unknown words in span elements', () => {
    renderWith(messageBuilder({})('hello world'));
    expect(screen.getByText('hello')).toBeInTheDocument();
    expect(screen.getByText('world')).toBeInTheDocument();
  });

  it('renders both markers and plain words in a single source string', () => {
    const mapping = {
      '<link/>': <a href="#">click here</a>,
    };
    renderWith(messageBuilder(mapping)('Please <link/> to continue'));
    expect(screen.getByRole('link', {name: 'click here'})).toBeInTheDocument();
    expect(screen.getByText('Please')).toBeInTheDocument();
    expect(screen.getByText('to')).toBeInTheDocument();
  });
});

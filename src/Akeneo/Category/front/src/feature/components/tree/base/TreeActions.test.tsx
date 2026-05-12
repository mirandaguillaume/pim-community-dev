import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {TreeActions} from './TreeActions';

const renderActions = (children?: React.ReactNode) =>
  render(
    <ThemeProvider theme={pimTheme}>
      <TreeActions>{children}</TreeActions>
    </ThemeProvider>
  );

describe('TreeActions', () => {
  it('renders without children', () => {
    const {container} = renderActions();
    expect(container.firstChild).toBeInTheDocument();
  });

  it('renders the children it receives', () => {
    renderActions(<button>Action</button>);
    expect(screen.getByRole('button', {name: 'Action'})).toBeInTheDocument();
  });

  it('renders multiple children', () => {
    renderActions(
      <>
        <button>First</button>
        <button>Second</button>
      </>
    );
    expect(screen.getByRole('button', {name: 'First'})).toBeInTheDocument();
    expect(screen.getByRole('button', {name: 'Second'})).toBeInTheDocument();
  });
});

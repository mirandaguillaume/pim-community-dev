import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {TreeIcon} from './TreeIcon';

// Mock icons so we can identify which one renders by data-testid
jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  FolderIcon: () => <span data-testid="folder-icon" />,
  FolderPlainIcon: () => <span data-testid="folder-plain-icon" />,
  FoldersIcon: () => <span data-testid="folders-icon" />,
  FoldersPlainIcon: () => <span data-testid="folders-plain-icon" />,
  LoaderIcon: () => <span data-testid="loader-icon" />,
}));

const renderIcon = (props: {isLoading: boolean; isLeaf: boolean; selected: boolean}) =>
  render(
    <ThemeProvider theme={pimTheme}>
      <TreeIcon {...props} />
    </ThemeProvider>
  );

describe('TreeIcon', () => {
  it('renders the loader icon when isLoading is true', () => {
    renderIcon({isLoading: true, isLeaf: false, selected: false});
    expect(screen.getByTestId('loader-icon')).toBeInTheDocument();
  });

  it('renders the selected leaf icon for isLeaf=true, selected=true', () => {
    renderIcon({isLoading: false, isLeaf: true, selected: true});
    expect(screen.getByTestId('folder-plain-icon')).toBeInTheDocument();
  });

  it('renders the unselected leaf icon for isLeaf=true, selected=false', () => {
    renderIcon({isLoading: false, isLeaf: true, selected: false});
    expect(screen.getByTestId('folder-icon')).toBeInTheDocument();
  });

  it('renders the selected folder icon for isLeaf=false, selected=true', () => {
    renderIcon({isLoading: false, isLeaf: false, selected: true});
    expect(screen.getByTestId('folders-plain-icon')).toBeInTheDocument();
  });

  it('renders the unselected folder icon for isLeaf=false, selected=false', () => {
    renderIcon({isLoading: false, isLeaf: false, selected: false});
    expect(screen.getByTestId('folders-icon')).toBeInTheDocument();
  });

  it('isLoading takes precedence over other props', () => {
    renderIcon({isLoading: true, isLeaf: true, selected: true});
    expect(screen.getByTestId('loader-icon')).toBeInTheDocument();
    expect(screen.queryByTestId('folder-plain-icon')).not.toBeInTheDocument();
  });
});

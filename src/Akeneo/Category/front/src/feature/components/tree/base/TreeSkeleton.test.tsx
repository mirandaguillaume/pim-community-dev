import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {TreeSkeleton} from './TreeSkeleton';

jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  SkeletonPlaceholder: () => <div data-testid="skeleton-placeholder" />,
}));

const renderSkeleton = () =>
  render(
    <ThemeProvider theme={pimTheme}>
      <TreeSkeleton />
    </ThemeProvider>
  );

describe('TreeSkeleton', () => {
  it('renders exactly five skeleton placeholders', () => {
    renderSkeleton();
    expect(screen.getAllByTestId('skeleton-placeholder')).toHaveLength(5);
  });
});

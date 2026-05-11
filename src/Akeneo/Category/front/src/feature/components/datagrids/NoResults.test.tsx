import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {NoResults} from './NoResults';

describe('NoResults', () => {
  it('renders the title prop', () => {
    renderWithProviders(<NoResults title="No categories found" subtitle="Try a different search" />);
    expect(screen.getByText('No categories found')).toBeInTheDocument();
  });

  it('renders the subtitle prop', () => {
    renderWithProviders(<NoResults title="No categories found" subtitle="Try a different search" />);
    expect(screen.getByText('Try a different search')).toBeInTheDocument();
  });

  it('renders both title and subtitle independently', () => {
    renderWithProviders(<NoResults title="Title A" subtitle="Subtitle B" />);
    expect(screen.getByText('Title A')).toBeInTheDocument();
    expect(screen.getByText('Subtitle B')).toBeInTheDocument();
  });
});

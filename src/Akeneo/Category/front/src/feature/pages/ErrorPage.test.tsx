import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {ErrorPage} from './ErrorPage';

describe('ErrorPage', () => {
  it('renders the error title translation key', () => {
    renderWithProviders(<ErrorPage error={new Error('oops')} />);
    expect(screen.getByText('akeneo.category.unknown_error.title')).toBeInTheDocument();
  });

  it('renders the error message translation key', () => {
    renderWithProviders(<ErrorPage error={new Error('oops')} />);
    expect(screen.getByText('akeneo.category.unknown_error.message')).toBeInTheDocument();
  });

  it('renders without crashing when error is null', () => {
    renderWithProviders(<ErrorPage error={null} />);
    expect(screen.getByText('akeneo.category.unknown_error.title')).toBeInTheDocument();
  });
});

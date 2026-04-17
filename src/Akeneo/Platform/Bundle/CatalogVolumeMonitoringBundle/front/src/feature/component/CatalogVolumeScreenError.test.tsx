import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {CatalogVolumeScreenError} from './CatalogVolumeScreenError';

test('it renders the title and message', () => {
  renderWithProviders(<CatalogVolumeScreenError title="An error occurred" message="Please try again" />);

  expect(screen.getByText('An error occurred')).toBeInTheDocument();
  expect(screen.getByText('Please try again')).toBeInTheDocument();
});

test('it renders different title and message values', () => {
  renderWithProviders(<CatalogVolumeScreenError title="Generic error" message="Contact support" />);

  expect(screen.getByText('Generic error')).toBeInTheDocument();
  expect(screen.getByText('Contact support')).toBeInTheDocument();
});

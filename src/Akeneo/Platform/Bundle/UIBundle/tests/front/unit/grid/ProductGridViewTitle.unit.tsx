import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {ProductGridViewTitle} from '../../../../Resources/public/js/grid/ProductGridViewTitle';

test('It renders the view name from children', () => {
  renderWithProviders(<ProductGridViewTitle type="default">My View</ProductGridViewTitle>);
  expect(screen.getByText('My View')).toBeInTheDocument();
});

test('It shows the public label for type "public"', () => {
  renderWithProviders(<ProductGridViewTitle type="public">My View</ProductGridViewTitle>);
  expect(screen.getByText(/pim_common\.public_view/)).toBeInTheDocument();
});

test('It shows the public label for type "view"', () => {
  renderWithProviders(<ProductGridViewTitle type="view">My View</ProductGridViewTitle>);
  expect(screen.getByText(/pim_common\.public_view/)).toBeInTheDocument();
});

test('It hides the public label for non-public types', () => {
  renderWithProviders(<ProductGridViewTitle type="default">My View</ProductGridViewTitle>);
  expect(screen.queryByText(/pim_common\.public_view/)).not.toBeInTheDocument();
});

test('It renders project details when projectDetails prop is provided', () => {
  renderWithProviders(
    <ProductGridViewTitle
      type="default"
      projectDetails={{dueDateLabel: 'Due date', dueDate: '2026-06-30', completionRatio: 50}}
    >
      My View
    </ProductGridViewTitle>
  );
  expect(screen.getByText(/Due date/)).toBeInTheDocument();
  expect(screen.getByText('50 %')).toBeInTheDocument();
});

import React from 'react';
import {render, screen} from '@testing-library/react';
import {ErrorBoundary} from './ErrorBoundary';

jest.mock('./pages/ErrorPage', () => ({
  ErrorPage: ({error}: {error: Error}) =>
    React.createElement('div', {'data-testid': 'error-page'}, error?.message ?? 'error'),
}));

const ThrowingComponent = () => {
  throw new Error('Something went wrong');
};

describe('ErrorBoundary', () => {
  beforeEach(() => {
    jest.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  it('renders its children when no error is thrown', () => {
    render(
      <ErrorBoundary>
        <div data-testid="child">Safe content</div>
      </ErrorBoundary>
    );
    expect(screen.getByTestId('child')).toBeInTheDocument();
  });

  it('renders ErrorPage when a child component throws', () => {
    render(
      <ErrorBoundary>
        <ThrowingComponent />
      </ErrorBoundary>
    );
    expect(screen.getByTestId('error-page')).toBeInTheDocument();
    expect(screen.getByTestId('error-page')).toHaveTextContent('Something went wrong');
  });

  it('does not render children when an error has been caught', () => {
    render(
      <ErrorBoundary>
        <ThrowingComponent />
      </ErrorBoundary>
    );
    expect(screen.queryByText('Safe content')).not.toBeInTheDocument();
  });
});

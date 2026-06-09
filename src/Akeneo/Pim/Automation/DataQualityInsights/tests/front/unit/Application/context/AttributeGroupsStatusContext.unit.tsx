import React from 'react';
import {render, renderHook} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  AttributeGroupsStatusProvider,
  useAttributeGroupsStatusContext,
} from '../../../../../front/src/application/context/AttributeGroupsStatusContext';

// The source imports from the barrel '../../infrastructure/hooks'; mock that barrel
// resolved from this test file's location
jest.mock('../../../../../front/src/infrastructure/hooks', () => ({
  useFetchAllAttributeGroupsStatus: jest.fn().mockReturnValue({
    load: jest.fn(),
    status: {outdoor: true, marketing: false},
  }),
}));

const wrapper = ({children}: {children: React.ReactNode}) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesProvider>
);

describe('AttributeGroupsStatusContext', () => {
  it('returns default status as empty object when no Provider', () => {
    const {result} = renderHook(() => useAttributeGroupsStatusContext(), {wrapper});
    expect(result.current.status).toEqual({});
  });

  it('Provider renders children', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsStatusProvider>
            <span data-testid="child">ok</span>
          </AttributeGroupsStatusProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    expect(document.querySelector('[data-testid="child"]')).toBeInTheDocument();
  });

  it('Provider exposes the mocked status', () => {
    const providerWrapper = ({children}: {children: React.ReactNode}) => (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsStatusProvider>{children}</AttributeGroupsStatusProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    const {result} = renderHook(() => useAttributeGroupsStatusContext(), {wrapper: providerWrapper});
    expect(result.current.status).toEqual({outdoor: true, marketing: false});
  });
});

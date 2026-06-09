import React from 'react';
import {renderHook} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  DashboardContextProvider,
  useDashboardContext,
} from '../../../../../front/src/application/context/DashboardContext';

const mockUpdateDashboardFilters = jest.fn();

jest.mock('../../../../../front/src/infrastructure/hooks/useInitDashboardContextState', () => ({
  useInitDashboardContextState: jest.fn().mockReturnValue({
    familyCode: 'mugs',
    category: null,
    updateDashboardFilters: mockUpdateDashboardFilters,
  }),
}));

describe('DashboardContext', () => {
  it('useDashboardContext throws when called outside a Provider', () => {
    const spy = jest.spyOn(console, 'error').mockImplementation(() => {});
    expect(() => {
      renderHook(() => useDashboardContext());
    }).toThrow('[DashboardContext]: dashboard context has not been properly initiated');
    spy.mockRestore();
  });

  it('Provider renders children and exposes the context value', () => {
    const providerWrapper = ({children}: {children: React.ReactNode}) => (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <DashboardContextProvider familyCode={null} category={null}>
            {children}
          </DashboardContextProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    const {result} = renderHook(() => useDashboardContext(), {wrapper: providerWrapper});
    expect(result.current.familyCode).toBe('mugs');
    expect(result.current.category).toBeNull();
  });
});

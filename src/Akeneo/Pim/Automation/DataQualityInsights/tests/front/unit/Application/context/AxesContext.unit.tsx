import React from 'react';
import {renderHook} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {useAxesContext, AxesContextProvider} from '../../../../../front/src/application/context/AxesContext';

const wrapper = ({children}: {children: React.ReactNode}) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesProvider>
);

describe('AxesContext', () => {
  it('returns default axes as empty array when no Provider', () => {
    const {result} = renderHook(() => useAxesContext(), {wrapper});
    expect(result.current.axes).toEqual([]);
  });

  it('AxesContextProvider passes axes to consumers', () => {
    const providerWrapper = ({children}: {children: React.ReactNode}) => (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AxesContextProvider axes={['enrichment', 'consistency']}>{children}</AxesContextProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    const {result} = renderHook(() => useAxesContext(), {wrapper: providerWrapper});
    expect(result.current.axes).toEqual(['enrichment', 'consistency']);
  });
});

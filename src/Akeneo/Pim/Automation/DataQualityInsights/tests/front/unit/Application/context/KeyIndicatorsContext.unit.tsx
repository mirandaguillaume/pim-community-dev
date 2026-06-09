import React from 'react';
import {renderHook} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  useKeyIndicatorsContext,
  KeyIndicatorsProvider,
} from '../../../../../front/src/application/context/KeyIndicatorsContext';

const wrapper = ({children}: {children: React.ReactNode}) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesProvider>
);

describe('KeyIndicatorsContext', () => {
  it('returns default tips as empty object when no Provider', () => {
    const {result} = renderHook(() => useKeyIndicatorsContext(), {wrapper});
    expect(result.current.tips).toEqual({});
  });

  it('KeyIndicatorsProvider passes tips to consumers', () => {
    const tips = {has_image: {step1: [{message: 'tip1'}]}};
    const providerWrapper = ({children}: {children: React.ReactNode}) => (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <KeyIndicatorsProvider tips={tips}>{children}</KeyIndicatorsProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
    const {result} = renderHook(() => useKeyIndicatorsContext(), {wrapper: providerWrapper});
    expect(result.current.tips).toEqual(tips);
  });
});

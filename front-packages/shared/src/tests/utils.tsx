import React, {FC, ReactElement, ReactNode} from 'react';
import {createRoot, Root} from 'react-dom/client';
import {ThemeProvider} from 'styled-components';
import {render, renderHook, RenderHookResult} from '@testing-library/react';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesContext} from '../DependenciesContext';
import {mockedDependencies} from './mockedDependencies';

const DefaultProviders: FC<{children?: ReactNode}> = ({children}) => (
  <DependenciesContext.Provider value={mockedDependencies}>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesContext.Provider>
);

const renderWithProviders = (ui: ReactElement) => render(ui, {wrapper: DefaultProviders});

let domRoot: Root | null = null;
const renderDOMWithProviders = (ui: ReactElement, container: HTMLElement) => {
  if (!domRoot) {
    domRoot = createRoot(container);
  }
  domRoot.render(<DefaultProviders>{ui}</DefaultProviders>);
};

const renderHookWithProviders: <R = any>(hook: () => R) => RenderHookResult<R, unknown> = <R,>(hook: () => R) =>
  renderHook<R, unknown>(hook, {wrapper: DefaultProviders});

export {renderWithProviders, renderDOMWithProviders, renderHookWithProviders, DefaultProviders};

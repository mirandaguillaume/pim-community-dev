import React, {FC, ReactElement, ReactNode} from 'react';
import ReactDOM from 'react-dom';
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

const renderDOMWithProviders = (ui: ReactElement, container: HTMLElement) =>
  ReactDOM.render(<DefaultProviders>{ui}</DefaultProviders>, container);

const renderHookWithProviders: <R = any>(hook: () => R) => RenderHookResult<R> = <R,>(hook: () => R) =>
  renderHook<R>(hook, {wrapper: DefaultProviders});

export {renderWithProviders, renderDOMWithProviders, renderHookWithProviders, DefaultProviders};

import React, {FC, ReactNode} from 'react';
import {createRoot, Root} from 'react-dom/client';
import {ThemeProvider} from 'styled-components';
import '@testing-library/jest-dom';
import {render, renderHook} from '@testing-library/react';
import {DependenciesProvider} from '../../../src/DependenciesProvider';
import {pimTheme} from 'akeneo-design-system';

const DefaultProviders: FC<{children?: ReactNode}> = ({children}) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesProvider>
);

const renderWithProviders = (ui: React.ReactElement) => render(ui, {wrapper: DefaultProviders});

let domRoot: Root | null = null;
let domRootContainer: HTMLElement | null = null;
const renderDOMWithProviders = (ui: React.ReactElement, container: HTMLElement) => {
  if (!domRoot || domRootContainer !== container) {
    if (domRoot) {
      domRoot.unmount();
    }
    domRoot = createRoot(container);
    domRootContainer = container;
  }
  domRoot.render(<DefaultProviders>{ui}</DefaultProviders>);
};

const renderHookWithProviders = (hook: () => any) => renderHook(hook, {wrapper: DefaultProviders});

const fetchMockResponseOnce = (requestUrl: string, responseBody: string) =>
  fetchMock.mockResponseOnce(request =>
    request.url === requestUrl
      ? Promise.resolve(responseBody)
      : Promise.reject(new Error(`Unexpected fetch URL: ${request.url}, expected: ${requestUrl}`))
  );

export {renderWithProviders, renderDOMWithProviders, renderHookWithProviders, fetchMockResponseOnce};

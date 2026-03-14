import React, {FC} from 'react';
import {createRoot, Root} from 'react-dom/client';
import {ThemeProvider} from 'styled-components';
import '@testing-library/jest-dom/extend-expect';
import {render, renderHook} from '@testing-library/react';
import {DependenciesProvider} from '../../../src/DependenciesProvider';
import {pimTheme} from 'akeneo-design-system';

const DefaultProviders: FC = ({children}) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>
  </DependenciesProvider>
);

const renderWithProviders = (ui: React.ReactElement) => render(ui, {wrapper: DefaultProviders});

let domRoot: Root | null = null;
const renderDOMWithProviders = (ui: React.ReactElement, container: HTMLElement) => {
  if (!domRoot) {
    domRoot = createRoot(container);
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

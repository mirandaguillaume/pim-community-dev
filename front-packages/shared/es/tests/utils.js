import React from 'react';
import { createRoot } from 'react-dom/client';
import { ThemeProvider } from 'styled-components';
import { render, renderHook } from '@testing-library/react';
import { pimTheme } from 'akeneo-design-system';
import { DependenciesContext } from '../DependenciesContext';
import { mockedDependencies } from './mockedDependencies';
var DefaultProviders = function (_a) {
    var children = _a.children;
    return (React.createElement(DependenciesContext.Provider, { value: mockedDependencies },
        React.createElement(ThemeProvider, { theme: pimTheme }, children)));
};
var renderWithProviders = function (ui) { return render(ui, { wrapper: DefaultProviders }); };
var domRoot = null;
var renderDOMWithProviders = function (ui, container) {
    if (!domRoot) {
        domRoot = createRoot(container);
    }
    domRoot.render(React.createElement(DefaultProviders, null, ui));
};
var renderHookWithProviders = function (hook) {
    return renderHook(hook, { wrapper: DefaultProviders });
};
export { renderWithProviders, renderDOMWithProviders, renderHookWithProviders, DefaultProviders };
//# sourceMappingURL=utils.js.map
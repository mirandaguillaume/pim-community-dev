var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
import React from 'react';
import { render } from '@testing-library/react';
import { ThemeProvider } from 'styled-components';
import { pimTheme } from '../theme/pim';
var wrapper = function (_a) {
    var children = _a.children;
    return (React.createElement(ThemeProvider, { theme: pimTheme }, children));
};
var customRender = function (ui, options) {
    return render(ui, __assign({ wrapper: wrapper }, options));
};
export * from '@testing-library/react';
export { customRender as render };
//# sourceMappingURL=test-util.js.map
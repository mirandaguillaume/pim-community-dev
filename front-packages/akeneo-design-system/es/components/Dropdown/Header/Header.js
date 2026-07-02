var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import styled from 'styled-components';
import { getColor } from '../../../theme';
import React from 'react';
var HeaderContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  box-sizing: border-box;\n  border-bottom: 1px solid ", ";\n  height: 34px;\n  line-height: 34px;\n  margin: 0 20px 10px 20px;\n  display: flex;\n  justify-content: space-between;\n  gap: 20px;\n  & > * {\n    flex-grow: 1;\n  }\n"], ["\n  box-sizing: border-box;\n  border-bottom: 1px solid ", ";\n  height: 34px;\n  line-height: 34px;\n  margin: 0 20px 10px 20px;\n  display: flex;\n  justify-content: space-between;\n  gap: 20px;\n  & > * {\n    flex-grow: 1;\n  }\n"])), getColor('brand', 100));
var Header = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children;
    return React.createElement(HeaderContainer, { ref: forwardedRef }, children);
});
export { Header };
var templateObject_1;
//# sourceMappingURL=Header.js.map
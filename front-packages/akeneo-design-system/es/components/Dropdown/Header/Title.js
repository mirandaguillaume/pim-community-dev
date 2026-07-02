var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import styled from 'styled-components';
import { getColor, getFontSize } from '../../../theme';
import React from 'react';
var TitleContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  font-size: ", ";\n  text-transform: uppercase;\n  color: ", ";\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"], ["\n  font-size: ", ";\n  text-transform: uppercase;\n  color: ", ";\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n"])), getFontSize('small'), getColor('brand', 100));
var Title = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children;
    return React.createElement(TitleContainer, { ref: forwardedRef }, children);
});
export { Title };
var templateObject_1;
//# sourceMappingURL=Title.js.map
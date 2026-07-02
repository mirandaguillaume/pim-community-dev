var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React from 'react';
import { SkeletonPlaceholder } from 'akeneo-design-system';
import styled from 'styled-components';
var Container = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  line-height: 34px;\n  margin: 0;\n  font-weight: normal;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  flex-grow: 1;\n\n  &:first-letter {\n    text-transform: ", ";\n  }\n"], ["\n  color: ", ";\n  font-size: ", ";\n  line-height: 34px;\n  margin: 0;\n  font-weight: normal;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  flex-grow: 1;\n\n  &:first-letter {\n    text-transform: ", ";\n  }\n"])), function (_a) {
    var theme = _a.theme;
    return theme.color.purple100;
}, function (_a) {
    var theme = _a.theme;
    return theme.fontSize.title;
}, function (_a) {
    var noTextTransform = _a.noTextTransform;
    return (noTextTransform ? 'initial' : 'capitalize');
});
var Title = function (_a) {
    var children = _a.children, showPlaceholder = _a.showPlaceholder, noTextTransform = _a.noTextTransform;
    return (React.createElement(Container, { noTextTransform: noTextTransform !== null && noTextTransform !== void 0 ? noTextTransform : false }, showPlaceholder ? React.createElement(SkeletonPlaceholder, null, children) : React.createElement(React.Fragment, null, children)));
};
export { Title };
var templateObject_1;
//# sourceMappingURL=Title.js.map
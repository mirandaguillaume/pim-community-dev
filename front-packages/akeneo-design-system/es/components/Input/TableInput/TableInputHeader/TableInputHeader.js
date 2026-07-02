var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
import React, { Children, useContext } from 'react';
import styled from 'styled-components';
import { getColor } from '../../../../theme';
import { TableInputContext } from '../TableInputContext';
var TableInputHeadTr = styled.tr(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  height: 40px;\n  background: ", ";\n  & > th {\n    border: 1px solid ", ";\n    border-left-width: 0;\n\n    &:first-child {\n      border-left-width: 1px;\n      position: sticky;\n      left: 0;\n      background: ", ";\n      z-index: 1;\n    }\n  }\n"], ["\n  height: 40px;\n  background: ", ";\n  & > th {\n    border: 1px solid ", ";\n    border-left-width: 0;\n\n    &:first-child {\n      border-left-width: 1px;\n      position: sticky;\n      left: 0;\n      background: ", ";\n      z-index: 1;\n    }\n  }\n"])), getColor('grey', 40), getColor('grey', 60), getColor('grey', 40));
var TableInputHeader = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var isDragAndDroppable = useContext(TableInputContext).isDragAndDroppable;
    return (React.createElement("thead", __assign({ ref: forwardedRef }, rest),
        React.createElement(TableInputHeadTr, null, Children.map(children, function (child, i) {
            return isDragAndDroppable && i === 0 && React.isValidElement(child)
                ? React.cloneElement(child, { colSpan: 2 })
                : child;
        }))));
});
TableInputHeader.displayName = 'TableInput.Header';
export { TableInputHeader };
var templateObject_1;
//# sourceMappingURL=TableInputHeader.js.map
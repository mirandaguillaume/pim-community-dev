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
import React from 'react';
import styled, { css } from 'styled-components';
import { getColor } from '../../../theme';
import { Image } from '../../../components';
var TableCellContainer = styled.td(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  color: ", ";\n  border-bottom: 1px solid ", ";\n  padding: 15px 10px;\n  max-width: 15vw;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  min-width: 0;\n\n  ", "\n"], ["\n  color: ", ";\n  border-bottom: 1px solid ", ";\n  padding: 15px 10px;\n  max-width: 15vw;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  min-width: 0;\n\n  ", "\n"])), getColor('grey', 140), getColor('grey', 60), function (props) {
    return props.rowTitle && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      color: ", ";\n      font-style: italic;\n    "], ["\n      color: ", ";\n      font-style: italic;\n    "])), getColor('brand', 100));
});
var TableCellInnerContainer = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  min-height: 24px;\n"], ["\n  display: flex;\n  align-items: center;\n  min-height: 24px;\n"])));
var TableCell = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, _b = _a.rowTitle, rowTitle = _b === void 0 ? false : _b, rest = __rest(_a, ["children", "rowTitle"]);
    return (React.createElement(TableCellContainer, __assign({ ref: forwardedRef, rowTitle: rowTitle }, rest),
        React.createElement(TableCellInnerContainer, null, React.Children.map(children, function (child) {
            if (!React.isValidElement(child) || child.type !== Image)
                return child;
            return React.cloneElement(child, {
                width: 44,
                height: 44,
            });
        }))));
});
export { TableCell };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=TableCell.js.map
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
import styled, { css } from 'styled-components';
import React from 'react';
import { getColor } from '../../../../theme';
import { highlightCell } from '../shared/highlightCell';
var TableInputCellContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n\n  ", "\n  padding: 0 10px;\n  height: 39px;\n  margin-right: 1px;\n\n  ", ";\n"], ["\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n\n  ", "\n  padding: 0 10px;\n  height: 39px;\n  margin-right: 1px;\n\n  ", ";\n"])), function (_a) {
    var rowTitle = _a.rowTitle;
    return rowTitle && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      color: ", ";\n      font-weight: bold;\n    "], ["\n      color: ", ";\n      font-weight: bold;\n    "])), getColor('brand', 100));
}, highlightCell);
var TableInputCellContent = function (_a) {
    var children = _a.children, _b = _a.rowTitle, rowTitle = _b === void 0 ? false : _b, _c = _a.highlighted, highlighted = _c === void 0 ? false : _c, _d = _a.inError, inError = _d === void 0 ? false : _d, rest = __rest(_a, ["children", "rowTitle", "highlighted", "inError"]);
    return (React.createElement(TableInputCellContainer, __assign({}, rest, { highlighted: highlighted, inError: inError, rowTitle: rowTitle }), children));
};
TableInputCellContent.displayName = 'TableInput.CellContent';
export { TableInputCellContent };
var templateObject_1, templateObject_2;
//# sourceMappingURL=TableInputCellContent.js.map
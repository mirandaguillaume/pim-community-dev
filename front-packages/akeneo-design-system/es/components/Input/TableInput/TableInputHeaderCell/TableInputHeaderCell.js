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
import styled from 'styled-components';
import { getColor } from '../../../../theme';
var TableInputTh = styled.th(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  font-weight: normal;\n  padding: 0 10px;\n  color: ", ";\n  text-align: left;\n  font-weight: bold;\n  white-space: nowrap;\n  min-width: 150px;\n  max-width: 250px;\n"], ["\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  font-weight: normal;\n  padding: 0 10px;\n  color: ", ";\n  text-align: left;\n  font-weight: bold;\n  white-space: nowrap;\n  min-width: 150px;\n  max-width: 250px;\n"])), getColor('grey', 140));
var TableInputHeaderCell = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    return (React.createElement(TableInputTh, __assign({ ref: forwardedRef }, rest), children));
});
TableInputHeaderCell.displayName = 'TableInput.HeaderCell';
export { TableInputHeaderCell };
var templateObject_1;
//# sourceMappingURL=TableInputHeaderCell.js.map
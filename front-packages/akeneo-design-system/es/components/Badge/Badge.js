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
import { getColor, getColorForLevel, getFontSize } from '../../theme';
var BadgeContainer = styled.span(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: inline-flex;\n  height: 18px;\n  line-height: 16px;\n  border: 1px solid;\n  padding: 0 6px;\n  border-radius: 2px;\n  text-transform: uppercase;\n  box-sizing: border-box;\n  background-color: ", ";\n  font-size: ", ";\n  font-weight: normal;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  overflow: hidden;\n  flex-shrink: 0;\n\n  ", "\n"], ["\n  display: inline-flex;\n  height: 18px;\n  line-height: 16px;\n  border: 1px solid;\n  padding: 0 6px;\n  border-radius: 2px;\n  text-transform: uppercase;\n  box-sizing: border-box;\n  background-color: ", ";\n  font-size: ", ";\n  font-weight: normal;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  overflow: hidden;\n  flex-shrink: 0;\n\n  ", "\n"])), getColor('white'), getFontSize('small'), function (_a) {
    var _b = _a.level, level = _b === void 0 ? 'primary' : _b;
    return css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n    color: ", ";\n    border-color: ", ";\n  "], ["\n    color: ", ";\n    border-color: ", ";\n  "])), getColorForLevel(level, 140), getColorForLevel(level, 100));
});
var Badge = React.forwardRef(function (_a, forwardedRef) {
    var _b = _a.level, level = _b === void 0 ? 'primary' : _b, children = _a.children, rest = __rest(_a, ["level", "children"]);
    return (React.createElement(BadgeContainer, __assign({ level: level, ref: forwardedRef }, rest), children));
});
export { Badge };
var templateObject_1, templateObject_2;
//# sourceMappingURL=Badge.js.map
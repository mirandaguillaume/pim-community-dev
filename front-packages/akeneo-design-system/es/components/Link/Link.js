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
import { getColor } from '../../theme';
var LinkContainer = styled.a(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  ", "\n\n  cursor: ", ";\n"], ["\n  ", "\n\n  cursor: ", ";\n"])), function (_a) {
    var decorated = _a.decorated, disabled = _a.disabled;
    return decorated
        ? css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n          font-weight: 400;\n          text-decoration: underline;\n          color: ", ";\n\n          ", ";\n        "], ["\n          font-weight: 400;\n          text-decoration: underline;\n          color: ", ";\n\n          ", ";\n        "])), disabled ? getColor('grey', 100) : getColor('brand', 100), !disabled && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n            &:hover {\n              color: ", ";\n            }\n\n            &:focus:not(:active) {\n              border-radius: 0px;\n              box-shadow: 0px 0px 0px 2px rgba(74, 144, 226, 0.3);\n              outline: none;\n            }\n\n            &:active {\n              outline: none;\n              color: ", ";\n            }\n          "], ["\n            &:hover {\n              color: ", ";\n            }\n\n            &:focus:not(:active) {\n              border-radius: 0px;\n              box-shadow: 0px 0px 0px 2px rgba(74, 144, 226, 0.3);\n              outline: none;\n            }\n\n            &:active {\n              outline: none;\n              color: ", ";\n            }\n          "])), getColor('brand', 120), getColor('brand', 140))) : css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n          text-decoration: none;\n          box-sizing: border-box;\n        "], ["\n          text-decoration: none;\n          box-sizing: border-box;\n        "])));
}, function (_a) {
    var disabled = _a.disabled;
    return (disabled ? 'not-allowed' : 'pointer');
});
var Link = React.forwardRef(function (_a, forwardedRef) {
    var _b = _a.disabled, disabled = _b === void 0 ? false : _b, _c = _a.decorated, decorated = _c === void 0 ? true : _c, _d = _a.target, target = _d === void 0 ? '_self' : _d, href = _a.href, children = _a.children, onClick = _a.onClick, rest = __rest(_a, ["disabled", "decorated", "target", "href", "children", "onClick"]);
    return (React.createElement(LinkContainer, __assign({ disabled: disabled, ref: forwardedRef, target: target, decorated: decorated, rel: target === '_blank' ? 'noopener noreferrer' : '', href: disabled ? undefined : href, onClick: disabled ? undefined : onClick }, rest), children));
});
export { Link };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=Link.js.map
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
import React, { Children, cloneElement, forwardRef, isValidElement, } from 'react';
import styled, { css } from 'styled-components';
import { getColor, getColorForLevel, getFontSize } from '../../theme';
var getColorStyle = function (_a) {
    var level = _a.level, ghost = _a.ghost, disabled = _a.disabled, active = _a.active;
    if (ghost) {
        return css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      color: ", ";\n      background-color: ", ";\n      border-color: ", ";\n\n      &:hover:not([disabled]) {\n        color: ", ";\n        background-color: ", ";\n        border-color: ", ";\n      }\n\n      &:active:not([disabled]) {\n        color: ", ";\n        border-color: ", ";\n      }\n    "], ["\n      color: ", ";\n      background-color: ", ";\n      border-color: ", ";\n\n      &:hover:not([disabled]) {\n        color: ", ";\n        background-color: ", ";\n        border-color: ", ";\n      }\n\n      &:active:not([disabled]) {\n        color: ", ";\n        border-color: ", ";\n      }\n    "])), getColorForLevel(level, disabled ? 80 : active ? 140 : 120), getColor('white'), getColorForLevel(level, disabled ? 60 : active ? 140 : 100), getColorForLevel(level, 140), getColorForLevel(level, 20), getColorForLevel(level, 120), getColorForLevel(level, 140), getColorForLevel(level, 140));
    }
    return css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n    color: ", ";\n    background-color: ", ";\n    border-color: ", ";\n\n    &:hover:not([disabled]) {\n      background-color: ", ";\n      border-color: ", ";\n    }\n\n    &:active:not([disabled]) {\n      background-color: ", ";\n      border-color: ", ";\n    }\n  "], ["\n    color: ", ";\n    background-color: ", ";\n    border-color: ", ";\n\n    &:hover:not([disabled]) {\n      background-color: ", ";\n      border-color: ", ";\n    }\n\n    &:active:not([disabled]) {\n      background-color: ", ";\n      border-color: ", ";\n    }\n  "])), getColor('white'), getColorForLevel(level, disabled ? 40 : active ? 140 : 100), getColorForLevel(level, disabled ? 40 : active ? 140 : 100), getColorForLevel(level, 120), getColorForLevel(level, 120), getColorForLevel(level, 140), getColorForLevel(level, 140));
};
var Container = styled.button(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: inline-flex;\n  align-items: center;\n  gap: 10px;\n  border-width: 1px;\n  font-size: ", ";\n  font-weight: 400;\n  text-transform: uppercase;\n  border-radius: 16px;\n  border-style: solid;\n  padding: ", ";\n  height: ", ";\n  cursor: ", ";\n  font-family: inherit;\n  transition: background-color 0.1s ease;\n  outline-style: none;\n  text-decoration: none;\n  white-space: nowrap;\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  ", "\n"], ["\n  display: inline-flex;\n  align-items: center;\n  gap: 10px;\n  border-width: 1px;\n  font-size: ", ";\n  font-weight: 400;\n  text-transform: uppercase;\n  border-radius: 16px;\n  border-style: solid;\n  padding: ", ";\n  height: ", ";\n  cursor: ", ";\n  font-family: inherit;\n  transition: background-color 0.1s ease;\n  outline-style: none;\n  text-decoration: none;\n  white-space: nowrap;\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  ", "\n"])), getFontSize('default'), function (_a) {
    var size = _a.size;
    return (size === 'small' ? '0 10px' : '0 15px');
}, function (_a) {
    var size = _a.size;
    return (size === 'small' ? '24px' : '32px');
}, function (_a) {
    var disabled = _a.disabled;
    return (disabled ? 'not-allowed' : 'pointer');
}, getColor('blue', 40), getColorStyle);
var Button = forwardRef(function (_a, forwardedRef) {
    var _b = _a.level, level = _b === void 0 ? 'primary' : _b, _c = _a.ghost, ghost = _c === void 0 ? false : _c, _d = _a.disabled, disabled = _d === void 0 ? false : _d, _e = _a.active, active = _e === void 0 ? false : _e, _f = _a.size, size = _f === void 0 ? 'default' : _f, href = _a.href, ariaDescribedBy = _a.ariaDescribedBy, ariaLabel = _a.ariaLabel, ariaLabelledBy = _a.ariaLabelledBy, children = _a.children, onClick = _a.onClick, _g = _a.type, type = _g === void 0 ? 'button' : _g, rest = __rest(_a, ["level", "ghost", "disabled", "active", "size", "href", "ariaDescribedBy", "ariaLabel", "ariaLabelledBy", "children", "onClick", "type"]);
    var handleAction = function (event) {
        if (disabled || undefined === onClick)
            return;
        onClick(event);
    };
    return (React.createElement(Container, __assign({ as: undefined !== href ? 'a' : 'button', level: level, ghost: ghost, disabled: disabled, active: active, size: size, "aria-describedby": ariaDescribedBy, "aria-disabled": disabled, "aria-label": ariaLabel, "aria-labelledby": ariaLabelledBy, ref: forwardedRef, role: "button", type: type, onClick: handleAction, href: disabled ? undefined : href }, rest), Children.map(children, function (child) {
        var _a;
        if (isValidElement(child)) {
            return cloneElement(child, { size: (_a = child.props.size) !== null && _a !== void 0 ? _a : 18 });
        }
        return child;
    })));
});
export { Button };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=Button.js.map
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
import { getColor, getFontSize } from '../../theme';
var getColorStyle = function (_a) {
    var disabled = _a.disabled;
    if (disabled) {
        return css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      border-color: ", ";\n      color: ", ";\n    "], ["\n      border-color: ", ";\n      color: ", ";\n    "])), getColor('grey', 100), getColor('grey', 100));
    }
    return css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n    background-color: ", ";\n    border-color: ", ";\n    color: ", ";\n  "], ["\n    background-color: ", ";\n    border-color: ", ";\n    color: ", ";\n  "])), getColor('white'), getColor('blue', 100), getColor('blue', 100));
};
var Container = styled.button(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  box-sizing: border-box;\n  width: 100%;\n  padding: 14px 20px;\n  border-style: solid;\n  border-width: 1px;\n  border-radius: 2px;\n  height: 50px;\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  font-family: inherit;\n  font-size: ", ";\n  font-weight: 400;\n  outline-style: none;\n  cursor: ", ";\n  white-space: nowrap;\n  text-transform: uppercase;\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  ", "\n"], ["\n  box-sizing: border-box;\n  width: 100%;\n  padding: 14px 20px;\n  border-style: solid;\n  border-width: 1px;\n  border-radius: 2px;\n  height: 50px;\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  font-family: inherit;\n  font-size: ", ";\n  font-weight: 400;\n  outline-style: none;\n  cursor: ", ";\n  white-space: nowrap;\n  text-transform: uppercase;\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  ", "\n"])), getFontSize('default'), function (_a) {
    var disabled = _a.disabled;
    return (disabled ? 'not-allowed' : 'pointer');
}, getColor('blue', 40), getColorStyle);
var ChildrenContainer = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  gap: 10px;\n"], ["\n  display: flex;\n  align-items: center;\n  gap: 10px;\n"])));
var ActionsContainer = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n"], ["\n  display: flex;\n  align-items: center;\n"])));
var BlockButton = forwardRef(function (_a, forwardedRef) {
    var _b;
    var icon = _a.icon, _c = _a.disabled, disabled = _c === void 0 ? false : _c, ariaDescribedBy = _a.ariaDescribedBy, ariaLabel = _a.ariaLabel, ariaLabelledBy = _a.ariaLabelledBy, children = _a.children, onClick = _a.onClick, rest = __rest(_a, ["icon", "disabled", "ariaDescribedBy", "ariaLabel", "ariaLabelledBy", "children", "onClick"]);
    var handleAction = function (event) {
        if (disabled || undefined === onClick)
            return;
        onClick(event);
    };
    return (React.createElement(Container, __assign({ disabled: disabled, "aria-describedby": ariaDescribedBy, "aria-disabled": disabled, "aria-label": ariaLabel, "aria-labelledby": ariaLabelledBy, ref: forwardedRef, role: "button", onClick: handleAction }, rest),
        React.createElement(ChildrenContainer, null, Children.map(children, function (child) {
            var _a;
            if (isValidElement(child)) {
                return cloneElement(child, { size: (_a = child.props.size) !== null && _a !== void 0 ? _a : 18 });
            }
            return child;
        })),
        React.createElement(ActionsContainer, null, isValidElement(icon) && cloneElement(icon, { size: (_b = icon.props.size) !== null && _b !== void 0 ? _b : 18 }))));
});
export { BlockButton };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=BlockButton.js.map
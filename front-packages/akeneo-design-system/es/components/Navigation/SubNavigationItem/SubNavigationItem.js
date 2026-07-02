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
import { getColor, getFontSize } from '../../../theme';
import { Tag } from '../../Tags/Tags';
var Container = styled.a(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  box-sizing: border-box;\n  cursor: ", ";\n  color: ", ";\n  display: flex;\n  height: 38px;\n  margin: 0;\n  outline: none;\n  text-decoration: none;\n  overflow: hidden;\n  line-height: 38px;\n\n  :hover {\n    color: ", ";\n  }\n  :focus:not(:active) {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"], ["\n  box-sizing: border-box;\n  cursor: ", ";\n  color: ", ";\n  display: flex;\n  height: 38px;\n  margin: 0;\n  outline: none;\n  text-decoration: none;\n  overflow: hidden;\n  line-height: 38px;\n\n  :hover {\n    color: ", ";\n  }\n  :focus:not(:active) {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"])), function (_a) {
    var disabled = _a.disabled;
    return (disabled ? 'not-allowed' : 'pointer');
}, function (_a) {
    var active = _a.active, disabled = _a.disabled;
    return disabled ? getColor('grey', 100) : active ? getColor('brand', 100) : getColor('grey', 140);
}, function (_a) {
    var disabled = _a.disabled;
    return !disabled && getColor('brand', 100);
}, getColor('blue', 40));
var Label = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  flex-shrink: 0;\n  margin-right: ", ";\n  max-width: ", ";\n  overflow: hidden;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  font-size: ", ";\n"], ["\n  flex-shrink: 0;\n  margin-right: ", ";\n  max-width: ", ";\n  overflow: hidden;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  font-size: ", ";\n"])), function (_a) {
    var hasTag = _a.hasTag;
    return (hasTag ? '10px' : '0px');
}, function (_a) {
    var hasTag = _a.hasTag;
    return (hasTag ? '84%' : '100%');
}, getFontSize('big'));
var SubNavigationItem = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, href = _a.href, disabled = _a.disabled, active = _a.active, onClick = _a.onClick, rest = __rest(_a, ["children", "href", "disabled", "active", "onClick"]);
    var handleClick = function (event) {
        if (disabled) {
            event.preventDefault();
            return;
        }
        onClick === null || onClick === void 0 ? void 0 : onClick(event);
    };
    var tag = null;
    var label = React.Children.map(children, function (child) {
        if (React.isValidElement(child) && child.type === Tag) {
            if (null === tag) {
                tag = child;
                return null;
            }
            throw new Error('You can only provide one component of type Tag.');
        }
        return child;
    });
    return (React.createElement(Container, __assign({ ref: forwardedRef, href: disabled ? undefined : href, active: active, disabled: disabled, "aria-disabled": disabled, onClick: handleClick }, rest),
        React.createElement(Label, { hasTag: !!tag }, label),
        tag));
});
export { SubNavigationItem };
var templateObject_1, templateObject_2;
//# sourceMappingURL=SubNavigationItem.js.map
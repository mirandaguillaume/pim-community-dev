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
import { CheckRoundIcon, DangerIcon, InfoRoundIcon } from '../../icons';
import { getColor } from '../../theme';
var getBackgroundColor = function (level) {
    switch (level) {
        case 'info':
            return getColor('blue', 10);
        case 'warning':
            return getColor('yellow', 10);
        case 'error':
            return getColor('red', 10);
        case 'success':
            return getColor('green', 10);
    }
};
var getFontColor = function (level, inline) {
    switch (level) {
        case 'info':
            return getColor('grey', 120);
        case 'warning':
            return getColor(inline ? 'grey' : 'yellow', 120);
        case 'error':
            return getColor('red', inline ? 100 : 120);
        case 'success':
            return getColor(inline ? 'grey' : 'green', 120);
    }
};
var getIconColor = function (level, inline) {
    switch (level) {
        case 'info':
            return getColor('blue', 100);
        case 'warning':
            return getColor('yellow', inline ? 100 : 120);
        case 'error':
            return getColor('red', inline ? 100 : 120);
        case 'success':
            return getColor('green', inline ? 100 : 120);
    }
};
var getIcon = function (level) {
    switch (level) {
        case 'info':
            return React.createElement(InfoRoundIcon, null);
        case 'warning':
            return React.createElement(DangerIcon, null);
        case 'error':
            return React.createElement(DangerIcon, null);
        case 'success':
            return React.createElement(CheckRoundIcon, null);
    }
};
var getSeparatorColor = function (level) {
    switch (level) {
        case 'info':
            return getColor('grey', 80);
        case 'warning':
            return getColor('yellow', 120);
        case 'error':
            return getColor('red', 120);
        case 'success':
            return getColor('green', 120);
    }
};
var getLinkColor = function (level, inline) {
    switch (level) {
        case 'info':
            return getColor('blue', 100);
        case 'warning':
            return getColor('yellow', 120);
        case 'error':
            return getColor('red', inline ? 100 : 120);
        case 'success':
            return getColor('green', inline ? 100 : 120);
    }
};
var Container = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  font-weight: 400;\n  padding-right: 20px;\n  color: ", ";\n\n  ", "\n\n  ", "\n"], ["\n  display: flex;\n  font-weight: 400;\n  padding-right: 20px;\n  color: ", ";\n\n  ", "\n\n  ", "\n"])), function (props) { return getFontColor(props.level, props.inline); }, function (props) {
    return !props.inline && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      min-height: 44px;\n      background-color: ", ";\n    "], ["\n      min-height: 44px;\n      background-color: ", ";\n    "])), getBackgroundColor(props.level));
}, function (_a) {
    var sticky = _a.sticky;
    return undefined !== sticky && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      position: sticky;\n      top: ", "px;\n      z-index: 1;\n    "], ["\n      position: sticky;\n      top: ", "px;\n      z-index: 1;\n    "])), sticky);
});
var IconContainer = styled.span(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  height: ", ";\n  margin: ", ";\n  color: ", ";\n"], ["\n  height: ", ";\n  margin: ", ";\n  color: ", ";\n"])), function (_a) {
    var inline = _a.inline;
    return (inline ? '16px' : '20px');
}, function (_a) {
    var inline = _a.inline;
    return (inline ? '2px 0' : '12px 10px');
}, function (props) { return getIconColor(props.level, props.inline); });
var TextContainer = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  padding-left: ", ";\n  white-space: break-spaces;\n  flex: 1;\n\n  a {\n    color: ", ";\n  }\n\n  ", "\n"], ["\n  padding-left: ", ";\n  white-space: break-spaces;\n  flex: 1;\n\n  a {\n    color: ", ";\n  }\n\n  ", "\n"])), function (_a) {
    var inline = _a.inline;
    return (inline ? '4px' : '10px');
}, function (_a) {
    var level = _a.level, inline = _a.inline;
    return getLinkColor(level, inline);
}, function (_a) {
    var inline = _a.inline, level = _a.level;
    return !inline && css(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n      margin: 12px 0;\n      border-left: 1px solid ", ";\n    "], ["\n      margin: 12px 0;\n      border-left: 1px solid ", ";\n    "])), getSeparatorColor(level));
});
var Helper = React.forwardRef(function (_a, forwardedRef) {
    var _b = _a.level, level = _b === void 0 ? 'info' : _b, _c = _a.inline, inline = _c === void 0 ? false : _c, icon = _a.icon, children = _a.children, rest = __rest(_a, ["level", "inline", "icon", "children"]);
    return (React.createElement(Container, __assign({ ref: forwardedRef, level: level, inline: inline }, rest),
        React.createElement(IconContainer, { inline: inline, level: level }, React.cloneElement(undefined === icon ? getIcon(level) : icon, { size: inline ? 16 : 20 })),
        React.createElement(TextContainer, { level: level, inline: inline }, children)));
});
export { Helper };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=Helper.js.map
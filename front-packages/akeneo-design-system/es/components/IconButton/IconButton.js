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
import { Button } from '../../components/Button/Button';
var IconButtonContainer = styled(Button)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: inline-flex;\n  align-items: center;\n  justify-content: center;\n  flex-shrink: 0;\n  padding: 0;\n  width: ", "px;\n  border-style: ", ";\n  ", ";\n"], ["\n  display: inline-flex;\n  align-items: center;\n  justify-content: center;\n  flex-shrink: 0;\n  padding: 0;\n  width: ", "px;\n  border-style: ", ";\n  ", ";\n"])), function (_a) {
    var size = _a.size;
    return (size === 'small' ? 24 : 32);
}, function (_a) {
    var borderless = _a.borderless, ghost = _a.ghost;
    return (!borderless && ghost ? 'solid' : 'none');
}, function (_a) {
    var borderless = _a.borderless;
    return borderless && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      background: transparent;\n    "], ["\n      background: transparent;\n    "])));
});
var getIconSize = function (size) {
    switch (size) {
        case 'small':
            return 16;
        case 'default':
            return 20;
    }
};
var IconButton = React.forwardRef(function (_a, forwardedRef) {
    var icon = _a.icon, _b = _a.size, size = _b === void 0 ? 'default' : _b, ghost = _a.ghost, rest = __rest(_a, ["icon", "size", "ghost"]);
    return (React.createElement(IconButtonContainer, __assign({ ref: forwardedRef, ghost: true === ghost || 'borderless' === ghost, borderless: 'borderless' === ghost, size: size }, rest), React.cloneElement(icon, { size: getIconSize(size) })));
});
export { IconButton };
var templateObject_1, templateObject_2;
//# sourceMappingURL=IconButton.js.map
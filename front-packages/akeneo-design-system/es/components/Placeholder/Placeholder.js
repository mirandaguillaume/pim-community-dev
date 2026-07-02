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
import React, { cloneElement } from 'react';
import styled from 'styled-components';
import { getColor, getFontSize } from '../../theme';
var CenteredHelperContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  padding: 0 20px;\n  display: flex;\n  align-items: center;\n  flex-direction: column;\n  gap: ", "px;\n"], ["\n  padding: 0 20px;\n  display: flex;\n  align-items: center;\n  flex-direction: column;\n  gap: ", "px;\n"])), function (_a) {
    var size = _a.size;
    return ('large' === size ? 20 : 5);
});
var CenteredHelperTitle = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  line-height: ", ";\n  text-align: center;\n"], ["\n  color: ", ";\n  font-size: ", ";\n  line-height: ", ";\n  text-align: center;\n"])), getColor('grey', 140), function (_a) {
    var size = _a.size;
    return getFontSize('large' === size ? 'title' : 'big');
}, function (_a) {
    var size = _a.size;
    return getFontSize('large' === size ? 'title' : 'big');
});
var Placeholder = function (_a) {
    var illustration = _a.illustration, title = _a.title, _b = _a.size, size = _b === void 0 ? 'default' : _b, children = _a.children, rest = __rest(_a, ["illustration", "title", "size", "children"]);
    return (React.createElement(CenteredHelperContainer, __assign({ size: size }, rest),
        cloneElement(illustration, { size: 'large' === size ? 256 : 120 }),
        React.createElement(CenteredHelperTitle, { size: size }, title),
        children));
};
export { Placeholder };
var templateObject_1, templateObject_2;
//# sourceMappingURL=Placeholder.js.map
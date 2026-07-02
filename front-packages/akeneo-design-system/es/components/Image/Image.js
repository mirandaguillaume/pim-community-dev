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
import { getColor, placeholderStyle } from '../../theme';
var EMPTY_IMAGE = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg"/>';
var ImageContainer = styled.img(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  background: ", ";\n  border: 1px solid ", ";\n  object-fit: ", ";\n  box-sizing: border-box;\n\n  ", "\n\n  ", "\n"], ["\n  background: ", ";\n  border: 1px solid ", ";\n  object-fit: ", ";\n  box-sizing: border-box;\n\n  ", "\n\n  ", "\n"])), getColor('white'), getColor('grey', 80), function (_a) {
    var fit = _a.fit;
    return fit;
}, function (_a) {
    var isStacked = _a.isStacked;
    return isStacked && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      box-shadow:\n        1px -1px 0 0 ", ",\n        2px -2px 0 0 ", ",\n        3px -3px 0 0 ", ",\n        4px -4px 0 0 ", ";\n    "], ["\n      box-shadow:\n        1px -1px 0 0 ", ",\n        2px -2px 0 0 ", ",\n        3px -3px 0 0 ", ",\n        4px -4px 0 0 ", ";\n    "])), getColor('white'), getColor('grey', 80), getColor('white'), getColor('grey', 80));
}, function (_a) {
    var isLoading = _a.isLoading;
    return isLoading && placeholderStyle;
});
var Image = React.forwardRef(function (_a, forwardedRef) {
    var _b = _a.fit, fit = _b === void 0 ? 'cover' : _b, _c = _a.isStacked, isStacked = _c === void 0 ? false : _c, src = _a.src, rest = __rest(_a, ["fit", "isStacked", "src"]);
    return (React.createElement(ImageContainer, __assign({ isLoading: null === src, src: src !== null && src !== void 0 ? src : EMPTY_IMAGE, ref: forwardedRef, fit: fit, isStacked: isStacked }, rest)));
});
export { Image };
var templateObject_1, templateObject_2;
//# sourceMappingURL=Image.js.map
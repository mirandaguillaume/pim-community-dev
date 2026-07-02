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
import React, { isValidElement } from 'react';
import styled from 'styled-components';
import { getColorAlternative, getFontSize } from '../../theme';
var convertTintToColorCode = function (str) {
    return str.replace(/_([a-z])/g, function (g) {
        return g[1].toUpperCase();
    });
};
var Tag = styled.li(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  border: 1px solid;\n  border-color: ", ";\n  color: ", ";\n  background-color: ", ";\n  height: 16px;\n  line-height: 16px;\n  padding: 0 6px;\n  display: inline-block;\n  border-radius: 2px;\n  font-size: ", ";\n  text-transform: uppercase;\n  overflow: hidden;\n  max-width: 200px;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n"], ["\n  border: 1px solid;\n  border-color: ", ";\n  color: ", ";\n  background-color: ", ";\n  height: 16px;\n  line-height: 16px;\n  padding: 0 6px;\n  display: inline-block;\n  border-radius: 2px;\n  font-size: ", ";\n  text-transform: uppercase;\n  overflow: hidden;\n  max-width: 200px;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n"])), function (_a) {
    var tint = _a.tint;
    return getColorAlternative(convertTintToColorCode(tint), 100);
}, function (_a) {
    var tint = _a.tint;
    return getColorAlternative(convertTintToColorCode(tint), 120);
}, function (_a) {
    var tint = _a.tint;
    return getColorAlternative(convertTintToColorCode(tint), 10);
}, getFontSize('small'));
var TagsContainer = styled.ul(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  flex-wrap: wrap;\n  padding-inline-start: 0;\n  margin-block-end: 0;\n  margin-block-start: 0;\n  list-style-type: none;\n  gap: 10px;\n"], ["\n  display: flex;\n  flex-wrap: wrap;\n  padding-inline-start: 0;\n  margin-block-end: 0;\n  margin-block-start: 0;\n  list-style-type: none;\n  gap: 10px;\n"])));
var Tags = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var getTitle = function (children) {
        var label = '';
        React.Children.map(children, function (child) {
            if (typeof child === 'string') {
                label += child;
            }
        });
        return label;
    };
    return (React.createElement(TagsContainer, __assign({ ref: forwardedRef }, rest), React.Children.map(children, function (child) {
        var _a, _b;
        if (isValidElement(child) && child.type === Tag) {
            return React.cloneElement(child, {
                title: ((_a = child.props) === null || _a === void 0 ? void 0 : _a.title) || getTitle((_b = child.props) === null || _b === void 0 ? void 0 : _b.children),
            });
        }
        throw new Error('A Tags element can only have Tag children');
    })));
});
export { Tags, Tag };
var templateObject_1, templateObject_2;
//# sourceMappingURL=Tags.js.map
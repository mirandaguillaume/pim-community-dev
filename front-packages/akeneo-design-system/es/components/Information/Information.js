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
import { getColor, getFontSize } from '../../theme';
var Container = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  align-items: stretch;\n  display: flex;\n  font-weight: 400;\n  padding-right: 15px;\n  color: ", ";\n  min-height: 100px;\n  background-color: ", ";\n"], ["\n  align-items: stretch;\n  display: flex;\n  font-weight: 400;\n  padding-right: 15px;\n  color: ", ";\n  min-height: 100px;\n  background-color: ", ";\n"])), getColor('grey120'), getColor('blue10'));
var IconContainer = styled.span(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  min-height: 80px;\n  display: flex;\n  align-items: center;\n  padding: 10px 20px;\n  margin: 10px 20px 10px 0px;\n  border-right: 1px solid ", ";\n"], ["\n  min-height: 80px;\n  display: flex;\n  align-items: center;\n  padding: 10px 20px;\n  margin: 10px 20px 10px 0px;\n  border-right: 1px solid ", ";\n"])), getColor('grey80'));
var HelperTitle = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 700;\n"], ["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 700;\n"])), getColor('grey140'), getFontSize('bigger'));
var ContentContainer = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  padding: 20px 0px;\n"], ["\n  padding: 20px 0px;\n"])));
var Information = React.forwardRef(function (_a, forwardedRef) {
    var illustration = _a.illustration, title = _a.title, children = _a.children, rest = __rest(_a, ["illustration", "title", "children"]);
    var resizedIllustration = isValidElement(illustration) && React.cloneElement(illustration, { size: 80 });
    return (React.createElement(Container, __assign({ ref: forwardedRef }, rest),
        React.createElement(IconContainer, null, resizedIllustration),
        React.createElement(ContentContainer, null,
            React.createElement(HelperTitle, null, title),
            children)));
});
var HighlightTitle = styled.span(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), getColor('brand', 100));
export { Information, HighlightTitle };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=Information.js.map
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
import styled, { css } from 'styled-components';
import { getColor, getFontSize } from '../../theme';
var Container = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  min-height: 80px;\n  border: 1px ", " solid;\n  box-sizing: border-box;\n  display: -ms-flexbox;\n  display: inline-flex;\n  opacity: ", ";\n  cursor: ", ";\n  background: ", "\n}\n\n;\n\n:hover {\n  border-color: ", ";\n  background: ", ";\n}\n\n:active {\n  outline: none;\n  background: ", ";\n  border-color: ", ";\n}\n\n:focus:not(:active) {\n  box-shadow: 0 0 0 2px ", ";\n  outline: none;\n}\n"], ["\n  min-height: 80px;\n  border: 1px ", " solid;\n  box-sizing: border-box;\n  display: -ms-flexbox;\n  display: inline-flex;\n  opacity: ", ";\n  cursor: ", ";\n  background: ", "\n}\n\n;\n\n:hover {\n  border-color: ", ";\n  background: ", ";\n}\n\n:active {\n  outline: none;\n  background: ", ";\n  border-color: ", ";\n}\n\n:focus:not(:active) {\n  box-shadow: 0 0 0 2px ", ";\n  outline: none;\n}\n"])), getColor('grey', 40), function (_a) {
    var disabled = _a.disabled;
    return disabled && 0.5;
}, function (_a) {
    var disabled = _a.disabled, onClick = _a.onClick;
    return (disabled ? 'not-allowed' : onClick !== undefined ? 'pointer' : 'inherit');
}, getColor('white'), function (_a) {
    var disabled = _a.disabled;
    return !disabled && getColor('grey', 60);
}, function (_a) {
    var disabled = _a.disabled;
    return !disabled && getColor('grey', 20);
}, function (_a) {
    var disabled = _a.disabled;
    return !disabled && getColor('grey', 20);
}, function (_a) {
    var disabled = _a.disabled;
    return !disabled && getColor('grey', 80);
}, getColor('blue', 40));
var IconContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  min-width: 80px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  border-right: 1px ", " solid;\n  margin: 10px 0;\n\n  svg {\n    color: ", ";\n  }\n"], ["\n  min-width: 80px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  border-right: 1px ", " solid;\n  margin: 10px 0;\n\n  svg {\n    color: ", ";\n  }\n"])), getColor('grey', 60), getColor('grey', 100));
var ContentContainer = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  margin: 15px;\n"], ["\n  margin: 15px;\n"])));
var TruncableMixin = css(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: -webkit-box;\n  -webkit-line-clamp: 2;\n  line-clamp: 2;\n  -webkit-box-orient: vertical;\n  box-orient: vertical;\n  overflow: hidden;\n  word-break: break-word;\n"], ["\n  display: -webkit-box;\n  -webkit-line-clamp: 2;\n  line-clamp: 2;\n  -webkit-box-orient: vertical;\n  box-orient: vertical;\n  overflow: hidden;\n  word-break: break-word;\n"])));
var Label = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  margin-bottom: 2px;\n\n  ", ";\n"], ["\n  color: ", ";\n  font-size: ", ";\n  margin-bottom: 2px;\n\n  ", ";\n"])), getColor('brand', 100), getFontSize('big'), TruncableMixin);
var Content = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n\n  ", ";\n"], ["\n  color: ", ";\n  font-size: ", ";\n\n  ", ";\n"])), getColor('grey', 100), getFontSize('small'), TruncableMixin);
var IconCardGrid = styled.div(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  display: grid;\n  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));\n  gap: 20px;\n"], ["\n  display: grid;\n  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));\n  gap: 20px;\n"])));
var IconCard = React.forwardRef(function (_a, forwardedRef) {
    var icon = _a.icon, label = _a.label, content = _a.content, onClick = _a.onClick, _b = _a.disabled, disabled = _b === void 0 ? false : _b, rest = __rest(_a, ["icon", "label", "content", "onClick", "disabled"]);
    var validIcon = isValidElement(icon) && React.cloneElement(icon, { size: 30 });
    return (React.createElement(Container, __assign({ ref: forwardedRef, disabled: disabled, onClick: onClick }, rest),
        React.createElement(IconContainer, null, validIcon),
        React.createElement(ContentContainer, null,
            React.createElement(Label, null, label),
            content && React.createElement(Content, null, content))));
});
export { IconCard, IconCardGrid };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7;
//# sourceMappingURL=IconCard.js.map
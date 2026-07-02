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
import styled, { css, keyframes } from 'styled-components';
import { getColor, getFontSize } from '../../theme';
import { CheckIcon, CheckPartialIcon } from '../../icons';
import { useId, useShortcut } from '../../hooks';
import { Key } from '../../shared';
var checkTick = keyframes(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  to {\n    stroke-dashoffset: 0;\n  }\n"], ["\n  to {\n    stroke-dashoffset: 0;\n  }\n"])));
var uncheckTick = keyframes(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  to {\n    stroke-dashoffset: 27px;\n  }\n"], ["\n  to {\n    stroke-dashoffset: 27px;\n  }\n"])));
var Container = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  line-height: 20px;\n"], ["\n  display: flex;\n  line-height: 20px;\n"])));
var TickIcon = styled(CheckIcon)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  animation: ", " 0.2s ease-in forwards;\n  opacity: 0;\n  stroke-dasharray: 27px;\n  stroke-dashoffset: 0;\n  transition-delay: 0.2s;\n  transition: opacity 0.1s ease-out;\n"], ["\n  animation: ", " 0.2s ease-in forwards;\n  opacity: 0;\n  stroke-dasharray: 27px;\n  stroke-dashoffset: 0;\n  transition-delay: 0.2s;\n  transition: opacity 0.1s ease-out;\n"])), uncheckTick);
var CheckboxContainer = styled.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  background-color: transparent;\n  height: 20px;\n  width: 20px;\n  border: 1px solid ", ";\n  border-radius: 3px;\n  overflow: hidden;\n  background-color: ", ";\n  transition: background-color 0.2s ease-out;\n  box-sizing: border-box;\n  color: ", ";\n  flex-shrink: 0;\n\n  ", "\n\n  ", "\n\n  ", "\n"], ["\n  background-color: transparent;\n  height: 20px;\n  width: 20px;\n  border: 1px solid ", ";\n  border-radius: 3px;\n  overflow: hidden;\n  background-color: ", ";\n  transition: background-color 0.2s ease-out;\n  box-sizing: border-box;\n  color: ", ";\n  flex-shrink: 0;\n\n  ", "\n\n  ", "\n\n  ", "\n"])), getColor('grey80'), getColor('grey20'), getColor('white'), function (props) {
    return props.checked && css(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n      background-color: ", ";\n      border-color: ", ";\n\n      ", " {\n        animation-delay: 0.2s;\n        animation: ", " 0.2s ease-out forwards;\n        stroke-dashoffset: 27px;\n        opacity: 1;\n        transition-delay: 0s;\n      }\n    "], ["\n      background-color: ", ";\n      border-color: ", ";\n\n      ", " {\n        animation-delay: 0.2s;\n        animation: ", " 0.2s ease-out forwards;\n        stroke-dashoffset: 27px;\n        opacity: 1;\n        transition-delay: 0s;\n      }\n    "])), getColor('blue100'), getColor('blue100'), TickIcon, checkTick);
}, function (props) {
    return props.checked &&
        props.readOnly && css(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n      background-color: ", ";\n      border-color: ", ";\n    "], ["\n      background-color: ", ";\n      border-color: ", ";\n    "])), getColor('blue20'), getColor('blue40'));
}, function (props) {
    return !props.checked &&
        props.readOnly && css(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n      background-color: ", ";\n      border-color: ", ";\n    "], ["\n      background-color: ", ";\n      border-color: ", ";\n    "])), getColor('grey60'), getColor('grey100'));
});
var LabelContainer = styled.label(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  color: ", ";\n  font-weight: 400;\n  font-size: ", ";\n  padding-left: 10px;\n\n  ", "\n"], ["\n  color: ", ";\n  font-weight: 400;\n  font-size: ", ";\n  padding-left: 10px;\n\n  ", "\n"])), getColor('grey140'), getFontSize('big'), function (props) {
    return props.readOnly && css(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n      color: ", ";\n    "], ["\n      color: ", ";\n    "])), getColor('grey100'));
});
var Checkbox = React.forwardRef(function (_a, forwardedRef) {
    var _b = _a.checked, checked = _b === void 0 ? false : _b, onChange = _a.onChange, _c = _a.readOnly, readOnly = _c === void 0 ? false : _c, children = _a.children, title = _a.title, ariaLabel = _a["aria-label"], tabIndex = _a.tabIndex, rest = __rest(_a, ["checked", "onChange", "readOnly", "children", "title", 'aria-label', "tabIndex"]);
    var checkboxId = useId('checkbox_');
    var labelId = useId('label_');
    var isChecked = true === checked;
    var isMixed = 'mixed' === checked;
    var handleChange = function (event) {
        if (!onChange || readOnly)
            return;
        switch (checked) {
            case true:
                onChange(false, event);
                break;
            case 'mixed':
            case false:
                onChange(true, event);
                break;
        }
        event.preventDefault();
        event.stopPropagation();
    };
    var ref = useShortcut(Key.Space, handleChange, forwardedRef);
    var forProps = children
        ? {
            'aria-labelledby': labelId,
            id: checkboxId,
        }
        : {};
    return (React.createElement(Container, __assign({}, rest),
        React.createElement(CheckboxContainer, __assign({ checked: isChecked || isMixed, readOnly: readOnly, title: title, role: "checkbox", ref: ref, "aria-checked": isChecked, tabIndex: undefined !== tabIndex ? tabIndex : readOnly ? -1 : 0, onClick: handleChange, "aria-label": ariaLabel }, forProps), isMixed ? React.createElement(CheckPartialIcon, { size: 18 }) : React.createElement(TickIcon, { size: 18 })),
        children ? (React.createElement(LabelContainer, { onClick: handleChange, id: labelId, readOnly: readOnly, htmlFor: checkboxId }, children)) : null));
});
export { Checkbox };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10;
//# sourceMappingURL=Checkbox.js.map
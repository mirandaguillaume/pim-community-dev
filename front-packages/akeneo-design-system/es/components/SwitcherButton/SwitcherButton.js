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
import React, { forwardRef } from 'react';
import styled, { css } from 'styled-components';
import { ArrowDownIcon, CloseIcon } from '../../icons';
import { CommonStyle, getColor, getFontSize } from '../../theme';
import { useId } from '../../hooks';
var SwitcherButtonContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n  > *:nth-child(2) {\n    opacity: 0;\n    transition: opacity 0.2s;\n  }\n  &:hover > *:nth-child(2) {\n    opacity: 1;\n  }\n"], ["\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n  > *:nth-child(2) {\n    opacity: 0;\n    transition: opacity 0.2s;\n  }\n  &:hover > *:nth-child(2) {\n    opacity: 1;\n  }\n"])));
var LabelAndValueContainer = styled.button(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  ", ";\n  border: none;\n  background: none;\n  cursor: pointer;\n  padding: 0;\n  display: flex;\n  align-items: baseline;\n  flex-direction: ", ";\n"], ["\n  ", ";\n  border: none;\n  background: none;\n  cursor: pointer;\n  padding: 0;\n  display: flex;\n  align-items: baseline;\n  flex-direction: ", ";\n"])), CommonStyle, function (_a) {
    var $inline = _a.$inline;
    return ($inline ? 'row' : 'column');
});
var Label = styled.label(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  cursor: pointer;\n  white-space: nowrap;\n  ", "\n"], ["\n  cursor: pointer;\n  white-space: nowrap;\n  ", "\n"])), function (_a) {
    var $inline = _a.$inline;
    return $inline
        ? css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n          margin-right: 3px;\n          color: ", ";\n        "], ["\n          margin-right: 3px;\n          color: ", ";\n        "])), getColor('grey', 140)) : css(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n          color: ", ";\n          text-transform: uppercase;\n          font-size: ", ";\n        "], ["\n          color: ", ";\n          text-transform: uppercase;\n          font-size: ", ";\n        "])), getColor('grey', 100), getFontSize('small'));
});
var LabelAndArrow = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  display: inline-flex;\n  align-items: center;\n"], ["\n  display: inline-flex;\n  align-items: center;\n"])));
var Value = styled.span(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  color: ", ";\n  margin-right: 5px;\n  text-align: left;\n"], ["\n  color: ", ";\n  margin-right: 5px;\n  text-align: left;\n"])), function (_a) {
    var $inline = _a.$inline;
    return ($inline ? getColor('brand', 100) : getColor('grey', 140));
});
var CloseButton = styled.button(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  border: none;\n  background: none;\n  width: 20px;\n  height: 20px;\n  cursor: pointer;\n  padding: 0;\n  flex-shrink: 0;\n"], ["\n  border: none;\n  background: none;\n  width: 20px;\n  height: 20px;\n  cursor: pointer;\n  padding: 0;\n  flex-shrink: 0;\n"])));
var SwitcherButton = forwardRef(function (_a, forwardedRef) {
    var label = _a.label, children = _a.children, onClick = _a.onClick, _b = _a.deletable, deletable = _b === void 0 ? false : _b, onDelete = _a.onDelete, _c = _a.inline, inline = _c === void 0 ? true : _c, rest = __rest(_a, ["label", "children", "onClick", "deletable", "onDelete", "inline"]);
    var buttonId = useId('button_');
    var handleDelete = function () { return deletable && (onDelete === null || onDelete === void 0 ? void 0 : onDelete()); };
    return (React.createElement(SwitcherButtonContainer, __assign({ ref: forwardedRef }, rest),
        React.createElement(LabelAndValueContainer, { type: "button", id: buttonId, onClick: onClick, "$inline": inline },
            React.createElement(Label, { htmlFor: buttonId, "$inline": inline }, label ? (inline ? "".concat(label, ":") : label) : ''),
            React.createElement(LabelAndArrow, null,
                React.createElement(Value, { "$inline": inline }, children),
                React.createElement(ArrowDownIcon, { size: inline ? 16 : 10 }))),
        deletable && (React.createElement(CloseButton, { onClick: handleDelete },
            React.createElement(CloseIcon, { size: 10 })))));
});
export { SwitcherButton };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8;
//# sourceMappingURL=SwitcherButton.js.map
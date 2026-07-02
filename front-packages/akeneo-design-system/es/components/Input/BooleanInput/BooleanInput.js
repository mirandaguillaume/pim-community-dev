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
import React, { useCallback } from 'react';
import styled, { css } from 'styled-components';
import { CommonStyle, getColor } from '../../../theme';
import { DangerIcon, EraseIcon, LockIcon } from '../../../icons';
var BooleanInputContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject([""], [""])));
var BooleanButton = styled.button(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  ", "\n  height: ", "px;\n  width: ", "px;\n  display: inline-block;\n  line-height: ", "px;\n  text-align: center;\n  vertical-align: middle;\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  background: ", ";\n\n  ", "\n"], ["\n  ", "\n  height: ", "px;\n  width: ", "px;\n  display: inline-block;\n  line-height: ", "px;\n  text-align: center;\n  vertical-align: middle;\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  background: ", ";\n\n  ", "\n"])), CommonStyle, function (_a) {
    var size = _a.size;
    return ('small' === size ? 30 : 40);
}, function (_a) {
    var size = _a.size;
    return ('small' === size ? 48 : 60);
}, function (_a) {
    var size = _a.size;
    return ('small' === size ? 26 : 36);
}, getColor('white'), function (_a) {
    var readOnly = _a.readOnly, invalid = _a.invalid;
    return readOnly
        ? css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n          border: 1px solid ", ";\n          color: ", ";\n          &:hover {\n            background: ", ";\n            color: ", ";\n          }\n        "], ["\n          border: 1px solid ", ";\n          color: ", ";\n          &:hover {\n            background: ", ";\n            color: ", ";\n          }\n        "])), getColor('grey', 60), getColor('grey', 80), getColor('white'), getColor('grey', 80)) : css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n          border: 1px solid ", ";\n          cursor: pointer;\n          &:hover {\n            background: ", ";\n            color: ", ";\n          }\n        "], ["\n          border: 1px solid ", ";\n          cursor: pointer;\n          &:hover {\n            background: ", ";\n            color: ", ";\n          }\n        "])), invalid ? getColor('red', 100) : getColor('grey', 80), getColor('grey', 20), getColor('grey', 140));
});
var NoButton = styled(BooleanButton)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  border-radius: 2px 0 0 2px;\n  border-right-width: 1px;\n\n  ", "\n"], ["\n  border-radius: 2px 0 0 2px;\n  border-right-width: 1px;\n\n  ", "\n"])), function (_a) {
    var value = _a.value, readOnly = _a.readOnly, invalid = _a.invalid;
    return value === false && css(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n      background: ", ";\n      border-color: ", ";\n      color: ", ";\n      &:hover {\n        background: ", ";\n        color: ", ";\n      }\n      &:active {\n        background: ", ";\n      }\n    "], ["\n      background: ", ";\n      border-color: ", ";\n      color: ", ";\n      &:hover {\n        background: ", ";\n        color: ", ";\n      }\n      &:active {\n        background: ", ";\n      }\n    "])), getColor('grey', readOnly ? 80 : 100), invalid ? getColor('red', 100) : getColor('grey', readOnly ? 80 : 100), getColor('white'), getColor('grey', readOnly ? 80 : 120), getColor('white'), getColor('grey', readOnly ? 80 : 140));
});
var YesButton = styled(BooleanButton)(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  border-radius: 0 2px 2px 0;\n  border-left-width: 0;\n\n  ", "\n"], ["\n  border-radius: 0 2px 2px 0;\n  border-left-width: 0;\n\n  ", "\n"])), function (_a) {
    var value = _a.value, readOnly = _a.readOnly, invalid = _a.invalid;
    return value === true && css(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n      background: ", ";\n      border-color: ", ";\n      color: ", ";\n\n      &:hover {\n        background: ", ";\n        color: ", ";\n      }\n\n      &:active {\n        background: ", ";\n      }\n    "], ["\n      background: ", ";\n      border-color: ", ";\n      color: ", ";\n\n      &:hover {\n        background: ", ";\n        color: ", ";\n      }\n\n      &:active {\n        background: ", ";\n      }\n    "])), getColor('green', readOnly ? 60 : 100), invalid ? getColor('red', 100) : getColor('grey', readOnly ? 60 : 100), getColor('white'), getColor('green', readOnly ? 60 : 120), getColor('white'), getColor('green', readOnly ? 60 : 140));
});
var ClearButton = styled.button(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  ", "\n  border: 0;\n  margin-left: 5px;\n  padding: 5px;\n  vertical-align: middle;\n  background: ", ";\n  color: ", ";\n  ", ";\n"], ["\n  ", "\n  border: 0;\n  margin-left: 5px;\n  padding: 5px;\n  vertical-align: middle;\n  background: ", ";\n  color: ", ";\n  ", ";\n"])), CommonStyle, getColor('white'), getColor('grey', 100), function (_a) {
    var readOnly = _a.readOnly;
    return !readOnly && 'cursor: pointer';
});
var BooleanInputEraseIcon = styled(EraseIcon)(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  vertical-align: bottom;\n  margin-right: 6px;\n"], ["\n  vertical-align: bottom;\n  margin-right: 6px;\n"])));
var IconContainer = styled.span(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  color: 1px solid ", ";\n  vertical-align: middle;\n  margin-left: 10px;\n"], ["\n  color: 1px solid ", ";\n  vertical-align: middle;\n  margin-left: 10px;\n"])), getColor('grey', 100));
var BooleanInputLockIcon = styled(LockIcon)(templateObject_12 || (templateObject_12 = __makeTemplateObject([""], [""])));
var ContainerInvalid = styled.div(templateObject_13 || (templateObject_13 = __makeTemplateObject(["\n  display: flex;\n  font-weight: 400;\n  padding-right: 20px;\n  color: ", ";\n"], ["\n  display: flex;\n  font-weight: 400;\n  padding-right: 20px;\n  color: ", ";\n"])), getColor('red', 100));
var IconInvalidContainer = styled.span(templateObject_14 || (templateObject_14 = __makeTemplateObject(["\n  margin: 2px 0;\n  color: ", ";\n"], ["\n  margin: 2px 0;\n  color: ", ";\n"])), getColor('red', 100));
var TextInvalidContainer = styled.div(templateObject_15 || (templateObject_15 = __makeTemplateObject(["\n  font-size: 11px;\n  padding-left: 4px;\n  white-space: break-spaces;\n  flex: 1;\n\n  a {\n    color: ", ";\n  }\n"], ["\n  font-size: 11px;\n  padding-left: 4px;\n  white-space: break-spaces;\n  flex: 1;\n\n  a {\n    color: ", ";\n  }\n"])), getColor('red', 100));
var BooleanInput = React.forwardRef(function (_a, forwardedRef) {
    var value = _a.value, _b = _a.readOnly, readOnly = _b === void 0 ? false : _b, onChange = _a.onChange, _c = _a.clearable, clearable = _c === void 0 ? false : _c, yesLabel = _a.yesLabel, noLabel = _a.noLabel, clearLabel = _a.clearLabel, invalid = _a.invalid, children = _a.children, _d = _a.size, size = _d === void 0 ? 'normal' : _d, rest = __rest(_a, ["value", "readOnly", "onChange", "clearable", "yesLabel", "noLabel", "clearLabel", "invalid", "children", "size"]);
    var handleChange = useCallback(function (value) {
        if (!onChange) {
            return;
        }
        onChange(value);
    }, [onChange, readOnly]);
    return (React.createElement(BooleanInputContainer, __assign({ role: "switch", "aria-checked": null === value ? undefined : value, ref: forwardedRef }, rest),
        React.createElement(NoButton, { value: value, readOnly: readOnly, "aria-readonly": readOnly, disabled: readOnly, onClick: function () {
                handleChange(false);
            }, title: noLabel, "aria-invalid": invalid, invalid: invalid, size: size }, noLabel),
        React.createElement(YesButton, { value: value, readOnly: readOnly, "aria-readonly": readOnly, disabled: readOnly, onClick: function () {
                handleChange(true);
            }, title: yesLabel, "aria-invalid": invalid, invalid: invalid, size: size }, yesLabel),
        value !== null && !readOnly && clearable && (React.createElement(ClearButton, { onClick: function () {
                handleChange(null);
            } },
            React.createElement(BooleanInputEraseIcon, { size: 16 }),
            clearLabel)),
        readOnly && (React.createElement(IconContainer, null,
            React.createElement(BooleanInputLockIcon, { size: 16 }))),
        invalid && children && (React.createElement(ContainerInvalid, null,
            React.createElement(IconInvalidContainer, null, React.cloneElement(React.createElement(DangerIcon, { size: 13 }))),
            React.createElement(TextInvalidContainer, null, children)))));
});
export { BooleanInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12, templateObject_13, templateObject_14, templateObject_15;
//# sourceMappingURL=BooleanInput.js.map
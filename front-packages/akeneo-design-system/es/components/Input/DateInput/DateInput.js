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
import React, { forwardRef, useCallback, useRef } from 'react';
import styled, { css } from 'styled-components';
import { Key } from '../../../shared';
import { LockIcon, DateIcon } from '../../../icons';
import { getColor, getFontSize } from '../../../theme';
import { useShortcut } from '../../../hooks';
var InputContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"])));
var Input = styled.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  z-index: 0;\n  width: 100%;\n  height: 40px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  box-sizing: border-box;\n  background: ", ";\n  color: ", ";\n  text-transform: uppercase;\n  font-size: ", ";\n  line-height: 40px;\n  padding: 0 ", " 0 15px;\n  outline-style: none;\n  cursor: ", ";\n\n  ", "\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n\n  &::-webkit-datetime-edit-fields-wrapper {\n    color: ", ";\n  }\n\n  &::-webkit-calendar-picker-indicator {\n    position: absolute;\n    top: 0;\n    left: 0;\n    right: 0;\n    bottom: 0;\n    width: auto;\n    height: auto;\n    color: transparent;\n    background: transparent;\n  }\n"], ["\n  z-index: 0;\n  width: 100%;\n  height: 40px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  box-sizing: border-box;\n  background: ", ";\n  color: ", ";\n  text-transform: uppercase;\n  font-size: ", ";\n  line-height: 40px;\n  padding: 0 ", " 0 15px;\n  outline-style: none;\n  cursor: ", ";\n\n  ", "\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n\n  &::-webkit-datetime-edit-fields-wrapper {\n    color: ", ";\n  }\n\n  &::-webkit-calendar-picker-indicator {\n    position: absolute;\n    top: 0;\n    left: 0;\n    right: 0;\n    bottom: 0;\n    width: auto;\n    height: auto;\n    color: transparent;\n    background: transparent;\n  }\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? getColor('red', 100) : getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 20) : getColor('white'));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 100) : getColor('grey', 140));
}, getFontSize('default'), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? '35px' : '15px');
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, function (_a) {
    var readOnly = _a.readOnly;
    return readOnly && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      overflow: hidden;\n      text-overflow: ellipsis;\n    "], ["\n      overflow: hidden;\n      text-overflow: ellipsis;\n    "])));
}, getColor('blue', 40), getColor('grey', 100), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 100) : getColor('grey', 140));
});
var IconContainer = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px 12px 12px 0;\n  padding-left: 12px;\n  pointer-events: none;\n  z-index: 1;\n\n  background: ", ";\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px 12px 12px 0;\n  padding-left: 12px;\n  pointer-events: none;\n  z-index: 1;\n\n  background: ", ";\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 20) : getColor('white'));
});
var ReadOnlyIcon = styled(LockIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), getColor('grey', 100));
var DateInput = forwardRef(function (_a, forwardedRef) {
    var invalid = _a.invalid, onChange = _a.onChange, value = _a.value, readOnly = _a.readOnly, onSubmit = _a.onSubmit, rest = __rest(_a, ["invalid", "onChange", "value", "readOnly", "onSubmit"]);
    var internalRef = useRef(null);
    forwardedRef = forwardedRef !== null && forwardedRef !== void 0 ? forwardedRef : internalRef;
    var handleClick = useCallback(function (event) {
        var _a;
        var input = event === null || event === void 0 ? void 0 : event.target;
        !readOnly && ((_a = input === null || input === void 0 ? void 0 : input.showPicker) === null || _a === void 0 ? void 0 : _a.call(input));
    }, [readOnly]);
    var handleChange = useCallback(function (event) {
        if (!readOnly && onChange) {
            onChange(event.currentTarget.value);
        }
    }, [readOnly, onChange]);
    var handleEnter = function () {
        !readOnly && (onSubmit === null || onSubmit === void 0 ? void 0 : onSubmit());
    };
    useShortcut(Key.Enter, handleEnter, forwardedRef);
    return (React.createElement(InputContainer, null,
        React.createElement(Input, __assign({ ref: forwardedRef, onChange: handleChange, type: "date", readOnly: readOnly, disabled: readOnly, "aria-invalid": invalid, invalid: invalid, title: value, value: value, pattern: "\\d{4}-\\d{2}-\\d{2}", onClick: handleClick }, rest)),
        React.createElement(IconContainer, { readOnly: readOnly },
            readOnly && React.createElement(ReadOnlyIcon, { size: 16 }),
            !readOnly && React.createElement(DateIcon, { size: 16 }))));
});
export { DateInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=DateInput.js.map
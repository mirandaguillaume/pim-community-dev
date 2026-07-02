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
import React, { useCallback, useRef } from 'react';
import styled, { css } from 'styled-components';
import { ArrowDownIcon, ArrowUpIcon, LockIcon } from '../../../icons';
import { Key } from '../../../shared';
import { getColor, getFontSize } from '../../../theme';
import { useShortcut } from '../../../hooks';
var NumberInputContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"])));
var Input = styled.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  width: 100%;\n  height: 40px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  background: ", ";\n  color: ", ";\n  font-size: ", ";\n  line-height: 40px;\n  padding: 0 ", " 0 15px;\n  box-sizing: border-box;\n  outline-style: none;\n  appearance: textfield;\n  ", "\n\n  &::-webkit-inner-spin-button,\n  &::-webkit-outer-spin-button {\n    -webkit-appearance: none;\n  }\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"], ["\n  width: 100%;\n  height: 40px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  background: ", ";\n  color: ", ";\n  font-size: ", ";\n  line-height: 40px;\n  padding: 0 ", " 0 15px;\n  box-sizing: border-box;\n  outline-style: none;\n  appearance: textfield;\n  ", "\n\n  &::-webkit-inner-spin-button,\n  &::-webkit-outer-spin-button {\n    -webkit-appearance: none;\n  }\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"])), function (_a) {
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
    return readOnly && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      overflow: hidden;\n      text-overflow: ellipsis;\n    "], ["\n      overflow: hidden;\n      text-overflow: ellipsis;\n    "])));
}, getColor('blue', 40), getColor('grey', 100));
var ReadOnlyIcon = styled(LockIcon)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px;\n  color: ", ";\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px;\n  color: ", ";\n"])), getColor('grey', 100));
var IncrementIconContainer = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 0 12px;\n  display: flex;\n  flex-direction: column;\n  height: 100%;\n  justify-content: center;\n  cursor: pointer;\n  color: ", ";\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 0 12px;\n  display: flex;\n  flex-direction: column;\n  height: 100%;\n  justify-content: center;\n  cursor: pointer;\n  color: ", ";\n"])), getColor('grey', 100));
var NumberInput = React.forwardRef(function (_a, forwardedRef) {
    var invalid = _a.invalid, onChange = _a.onChange, readOnly = _a.readOnly, step = _a.step, value = _a.value, onSubmit = _a.onSubmit, rest = __rest(_a, ["invalid", "onChange", "readOnly", "step", "value", "onSubmit"]);
    var internalRef = useRef(null);
    forwardedRef = forwardedRef !== null && forwardedRef !== void 0 ? forwardedRef : internalRef;
    var handleChange = useCallback(function (event) {
        if (!readOnly && onChange)
            onChange(event.currentTarget.value);
    }, [readOnly, onChange]);
    var handleEnter = function () {
        !readOnly && (onSubmit === null || onSubmit === void 0 ? void 0 : onSubmit());
    };
    useShortcut(Key.Enter, handleEnter, forwardedRef);
    var handleIncrement = useCallback(function () {
        if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && !readOnly && onChange) {
            forwardedRef.current.stepUp(step);
            onChange(forwardedRef.current.value);
        }
    }, [forwardedRef, step, readOnly, value, onChange]);
    var handleDecrement = useCallback(function () {
        if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && !readOnly && onChange) {
            forwardedRef.current.stepDown(step);
            onChange(forwardedRef.current.value);
        }
    }, [forwardedRef, step, readOnly, value, onChange]);
    return (React.createElement(NumberInputContainer, null,
        React.createElement(Input, __assign({ ref: forwardedRef, onChange: handleChange, type: "number", readOnly: readOnly, disabled: readOnly, "aria-invalid": invalid, invalid: invalid, autoComplete: "off", value: value, title: value }, rest)),
        readOnly && React.createElement(ReadOnlyIcon, { size: 16 }),
        !readOnly && (React.createElement(IncrementIconContainer, null,
            React.createElement(ArrowUpIcon, { size: 16, "data-testid": "increment-number-input", onClick: handleIncrement }),
            React.createElement(ArrowDownIcon, { size: 16, "data-testid": "decrement-number-input", onClick: handleDecrement })))));
});
export { NumberInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=NumberInput.js.map
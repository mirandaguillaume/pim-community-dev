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
import React, { forwardRef, useCallback } from 'react';
import styled, { css } from 'styled-components';
import { DangerIcon, LockIcon } from '../../../icons';
import { getColor } from '../../../theme';
import { isValidColor, convertColorToLongHexColor } from './Color';
var ColorInputContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  padding: 12px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: 74px;\n  gap: 10px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n  ", "\n"], ["\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  padding: 12px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: 74px;\n  gap: 10px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n  ", "\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? getColor('red', 100) : getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 20) : getColor('white'));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, function (_a) {
    var readOnly = _a.readOnly;
    return !readOnly && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      &:focus-within {\n        box-shadow: 0 0 0 2px ", ";\n      }\n    "], ["\n      &:focus-within {\n        box-shadow: 0 0 0 2px ", ";\n      }\n    "])), getColor('blue', 40));
});
var ColorPicker = styled.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  width: 47px;\n  height: 47px;\n  border: none;\n  padding: 0;\n  ::-moz-color-swatch-wrapper {\n    padding: 0;\n  }\n  ::-webkit-color-swatch-wrapper {\n    padding: 0;\n  }\n  ::-webkit-color-swatch {\n    border: none;\n  }\n  ::-moz-color-swatch {\n    border: none;\n  }\n"], ["\n  width: 47px;\n  height: 47px;\n  border: none;\n  padding: 0;\n  ::-moz-color-swatch-wrapper {\n    padding: 0;\n  }\n  ::-webkit-color-swatch-wrapper {\n    padding: 0;\n  }\n  ::-webkit-color-swatch {\n    border: none;\n  }\n  ::-moz-color-swatch {\n    border: none;\n  }\n"])));
var TextInput = styled.input(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  border: none;\n  flex: 1;\n  outline: none;\n  color: ", ";\n  background: transparent;\n  cursor: ", ";\n  height: 100%;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"], ["\n  border: none;\n  flex: 1;\n  outline: none;\n  color: ", ";\n  background: transparent;\n  cursor: ", ";\n  height: 100%;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 100) : getColor('grey', 140));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, getColor('grey', 100));
var ReadOnlyIcon = styled(LockIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  margin-left: 4px;\n"], ["\n  margin-left: 4px;\n"])));
var ErrorIcon = styled(DangerIcon)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  padding: 0 15px 0 16px;\n  box-sizing: content-box;\n"], ["\n  padding: 0 15px 0 16px;\n  box-sizing: content-box;\n"])));
var ColorInput = forwardRef(function (_a, forwardedRef) {
    var invalid = _a.invalid, onChange = _a.onChange, value = _a.value, readOnly = _a.readOnly, rest = __rest(_a, ["invalid", "onChange", "value", "readOnly"]);
    var handleChange = useCallback(function (event) {
        if (!readOnly && onChange)
            onChange(event.currentTarget.value);
    }, [readOnly, onChange]);
    if (!value.startsWith('#')) {
        value = "#".concat(value);
    }
    return (React.createElement(ColorInputContainer, { invalid: invalid || !isValidColor(value), readOnly: readOnly },
        isValidColor(value) ? (React.createElement(ColorPicker, { type: "color", value: convertColorToLongHexColor(value), onChange: handleChange, disabled: readOnly })) : (React.createElement(ErrorIcon, { role: "alert", size: 16 })),
        React.createElement(TextInput, __assign({ ref: forwardedRef, value: value, onChange: handleChange, type: "text", readOnly: readOnly, disabled: readOnly, "aria-invalid": invalid || !isValidColor(value) }, rest)),
        readOnly && React.createElement(ReadOnlyIcon, { size: 16 })));
});
export { ColorInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=ColorInput.js.map
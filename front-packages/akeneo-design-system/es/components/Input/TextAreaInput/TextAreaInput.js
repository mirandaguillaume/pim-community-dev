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
import { LockIcon } from '../../../icons';
import { getColor, getFontSize } from '../../../theme';
import { RichTextEditor } from './RichTextEditor';
var TextAreaInputContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"])));
var CommonStyle = css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  color: ", ";\n  font-size: ", ";\n  line-height: 20px;\n  width: 100%;\n  box-sizing: border-box;\n  font-family: inherit;\n  outline-style: none;\n  background: ", ";\n  cursor: ", ";\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  a.rdw-dropdown-selectedtext > span {\n    color: ", ";\n  }\n"], ["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  color: ", ";\n  font-size: ", ";\n  line-height: 20px;\n  width: 100%;\n  box-sizing: border-box;\n  font-family: inherit;\n  outline-style: none;\n  background: ", ";\n  cursor: ", ";\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  a.rdw-dropdown-selectedtext > span {\n    color: ", ";\n  }\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? getColor('red', 100) : getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 100) : getColor('grey', 140));
}, getFontSize('default'), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 20) : getColor('white'));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, getColor('blue', 40), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 100) : getColor('grey', 140));
});
var RichTextEditorContainer = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", "\n  padding: 0;\n  padding-bottom: 10px;\n\n  & .rdw-editor-main {\n    min-height: 200px;\n    max-height: 400px;\n    padding: 0 30px 10px 15px;\n  }\n\n  & .rdw-option-wrapper {\n    min-width: 30px;\n    height: 30px;\n  }\n\n  & .rdw-editor-toolbar {\n    border: none;\n    padding: 0;\n    margin: 0;\n    padding: 5px 30px 0 0;\n    border-radius: 0;\n    border-bottom: 1px solid ", ";\n  }\n\n  & .rdw-dropdown-wrapper:hover,\n  & .rdw-option-wrapper:hover,\n  & .rdw-dropdown-optionwrapper:hover {\n    box-shadow: none;\n  }\n"], ["\n  ", "\n  padding: 0;\n  padding-bottom: 10px;\n\n  & .rdw-editor-main {\n    min-height: 200px;\n    max-height: 400px;\n    padding: 0 30px 10px 15px;\n  }\n\n  & .rdw-option-wrapper {\n    min-width: 30px;\n    height: 30px;\n  }\n\n  & .rdw-editor-toolbar {\n    border: none;\n    padding: 0;\n    margin: 0;\n    padding: 5px 30px 0 0;\n    border-radius: 0;\n    border-bottom: 1px solid ", ";\n  }\n\n  & .rdw-dropdown-wrapper:hover,\n  & .rdw-option-wrapper:hover,\n  & .rdw-dropdown-optionwrapper:hover {\n    box-shadow: none;\n  }\n"])), CommonStyle, function (_a) {
    var invalid = _a.invalid;
    return (invalid ? getColor('red', 100) : getColor('grey', 80));
});
var Textarea = styled.textarea(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  ", "\n  resize: none;\n  height: 200px;\n  padding: 10px 30px 10px 15px;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"], ["\n  ", "\n  resize: none;\n  height: 200px;\n  padding: 10px 30px 10px 15px;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"])), CommonStyle, getColor('grey', 100));
var ReadOnlyIcon = styled(LockIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px;\n  color: ", ";\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px;\n  color: ", ";\n"])), getColor('grey', 100));
var CharacterLeftLabel = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  font-size: ", ";\n  align-self: flex-end;\n  color: ", ";\n"], ["\n  font-size: ", ";\n  align-self: flex-end;\n  color: ", ";\n"])), getFontSize('small'), getColor('grey', 100));
var TextAreaInput = React.forwardRef(function (_a, forwardedRef) {
    var value = _a.value, invalid = _a.invalid, onChange = _a.onChange, readOnly = _a.readOnly, characterLeftLabel = _a.characterLeftLabel, _b = _a.isRichText, isRichText = _b === void 0 ? false : _b, richTextEditorProps = _a.richTextEditorProps, rest = __rest(_a, ["value", "invalid", "onChange", "readOnly", "characterLeftLabel", "isRichText", "richTextEditorProps"]);
    var handleChange = useCallback(function (event) {
        if (!readOnly && onChange)
            onChange(event.currentTarget.value);
    }, [readOnly, onChange]);
    return (React.createElement(TextAreaInputContainer, null,
        isRichText ? (React.createElement(RichTextEditorContainer, { readOnly: readOnly, invalid: invalid },
            React.createElement(RichTextEditor, __assign({ readOnly: readOnly, value: value }, richTextEditorProps, { onChange: function (value) { return onChange === null || onChange === void 0 ? void 0 : onChange(value); } })))) : (React.createElement(Textarea, __assign({ ref: forwardedRef, value: value, onChange: handleChange, type: "text", readOnly: readOnly, disabled: readOnly, "aria-invalid": invalid, invalid: invalid }, rest))),
        readOnly && React.createElement(ReadOnlyIcon, { size: 16 }),
        characterLeftLabel && React.createElement(CharacterLeftLabel, null, characterLeftLabel)));
});
export { TextAreaInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=TextAreaInput.js.map
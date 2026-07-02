var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React, { useEffect } from 'react';
import styled from 'styled-components';
import { CloseIcon, LockIcon } from '../../../icons';
import { getColor, getFontSize } from '../../../theme';
import { IconButton } from '../../IconButton/IconButton';
import { useBooleanState, useShortcut, useTheme } from '../../../hooks';
import { Key } from '../../../shared';
var Container = styled.ul(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  padding: 4px 30px 4px 4px;\n  display: flex;\n  flex-wrap: wrap;\n  min-height: 40px;\n  gap: 5px;\n  box-sizing: border-box;\n  background: ", ";\n  position: relative;\n  margin: 0;\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"], ["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  padding: 4px 30px 4px 4px;\n  display: flex;\n  flex-wrap: wrap;\n  min-height: 40px;\n  gap: 5px;\n  box-sizing: border-box;\n  background: ", ";\n  position: relative;\n  margin: 0;\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? getColor('red', 100) : getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 20) : getColor('white'));
}, getColor('blue', 40));
var Chip = styled.li(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  list-style-type: none;\n  padding: 3px 15px;\n  padding-left: ", ";\n  border: 1px ", " solid;\n  background-color: ", ";\n  display: flex;\n  align-items: center;\n  height: 30px;\n  box-sizing: border-box;\n  color: ", ";\n"], ["\n  list-style-type: none;\n  padding: 3px 15px;\n  padding-left: ", ";\n  border: 1px ", " solid;\n  background-color: ", ";\n  display: flex;\n  align-items: center;\n  height: 30px;\n  box-sizing: border-box;\n  color: ", ";\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? '15px' : '4px');
}, function (_a) {
    var isErrored = _a.isErrored;
    return (isErrored ? getColor('red', 80) : getColor('grey', 80));
}, function (_a) {
    var isSelected = _a.isSelected, isErrored = _a.isErrored;
    return isErrored ? getColor('red', 20) : isSelected ? getColor('grey', 40) : getColor('grey', 20);
}, function (_a) {
    var readOnly = _a.readOnly, isErrored = _a.isErrored, isLocked = _a.isLocked;
    return isErrored ? getColor('red', 100) : readOnly || isLocked ? getColor('grey', 100) : getColor('grey', 140);
});
var Input = styled.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  width: 100%;\n  height: 100%;\n  border: 0;\n  outline: 0;\n  color: ", ";\n  background-color: transparent;\n  font-size: ", ";\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"], ["\n  width: 100%;\n  height: 100%;\n  border: 0;\n  outline: 0;\n  color: ", ";\n  background-color: transparent;\n  font-size: ", ";\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"])), getColor('grey', 120), getFontSize('default'), getColor('grey', 100));
var InputContainer = styled.li(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  list-style-type: none;\n  color: ", ";\n  border: 0;\n  flex: 1;\n  padding: 0;\n  align-items: center;\n  display: flex;\n\n  :first-child > ", " {\n    padding-left: 11px;\n  }\n"], ["\n  list-style-type: none;\n  color: ", ";\n  border: 0;\n  flex: 1;\n  padding: 0;\n  align-items: center;\n  display: flex;\n\n  :first-child > ", " {\n    padding-left: 11px;\n  }\n"])), getColor('grey', 120), Input);
var ReadOnlyIcon = styled(LockIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 11px;\n  color: ", ";\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 11px;\n  color: ", ";\n"])), getColor('grey', 100));
var LockedValueIcon = styled(LockIcon)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  padding-right: 5px;\n"], ["\n  padding-right: 5px;\n"])));
var RemoveButton = styled(IconButton)(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  background-color: transparent;\n  margin-left: -3px;\n  margin-right: 1px;\n  color: ", ";\n"], ["\n  background-color: transparent;\n  margin-left: -3px;\n  margin-right: 1px;\n  color: ", ";\n"])), function (_a) {
    var isErrored = _a.isErrored;
    return (isErrored ? getColor('red', 100) : getColor('grey', 100));
});
var ChipInput = React.forwardRef(function (_a, forwardedRef) {
    var id = _a.id, value = _a.value, invalidValue = _a.invalidValue, invalid = _a.invalid, readOnly = _a.readOnly, placeholder = _a.placeholder, searchValue = _a.searchValue, removeLabel = _a.removeLabel, onRemove = _a.onRemove, onSearchChange = _a.onSearchChange, onFocus = _a.onFocus, lockedValues = _a.lockedValues;
    var theme = useTheme();
    var _b = useBooleanState(), isLastSelected = _b[0], selectLast = _b[1], unselectLast = _b[2];
    var handleChange = function (event) { return onSearchChange(event.target.value); };
    var handleBackspace = function () {
        if ('' !== searchValue || 0 === value.length) {
            return;
        }
        if (isLastSelected) {
            onRemove(value[value.length - 1].code);
        }
        else {
            selectLast();
        }
    };
    useEffect(function () {
        unselectLast();
    }, [value, searchValue]);
    useShortcut(Key.Backspace, handleBackspace, forwardedRef);
    return (React.createElement(Container, { invalid: invalid, readOnly: readOnly },
        value.map(function (chip, index) { return (React.createElement(Chip, { key: chip.code, readOnly: readOnly, isLocked: lockedValues === null || lockedValues === void 0 ? void 0 : lockedValues.includes(chip.code), isErrored: invalidValue.includes(chip.code), isSelected: index === value.length - 1 && isLastSelected },
            !readOnly && !(lockedValues === null || lockedValues === void 0 ? void 0 : lockedValues.includes(chip.code)) && (React.createElement(RemoveButton, { title: removeLabel, ghost: "borderless", size: "small", level: "tertiary", icon: React.createElement(CloseIcon, { color: invalidValue.includes(chip.code) ? theme.color.red100 : theme.color.grey100 }), onClick: function () { return onRemove(chip.code); }, isErrored: invalidValue.includes(chip.code) })),
            (lockedValues === null || lockedValues === void 0 ? void 0 : lockedValues.includes(chip.code)) && React.createElement(LockedValueIcon, { size: 16 }),
            chip.label)); }),
        React.createElement(InputContainer, null,
            React.createElement(Input, { type: "text", id: id, value: searchValue, ref: forwardedRef, placeholder: value.length === 0 ? placeholder : undefined, onChange: handleChange, onBlur: unselectLast, "aria-invalid": invalid, readOnly: readOnly, disabled: readOnly, onFocus: onFocus }),
            readOnly && React.createElement(ReadOnlyIcon, { size: 16 }))));
});
export { ChipInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7;
//# sourceMappingURL=ChipInput.js.map
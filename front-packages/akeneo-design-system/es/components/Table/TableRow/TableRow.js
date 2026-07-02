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
import React, { forwardRef, useContext, } from 'react';
import styled, { css } from 'styled-components';
import { getColor } from '../../../theme';
import { Checkbox } from '../../../components';
import { TableContext } from '../TableContext';
import { TableCell } from '../TableCell/TableCell';
import { RowIcon, DangerIcon, LockIcon } from '../../../icons';
import { usePlaceholderPosition } from '../../../hooks/usePlaceholderPosition';
var RowContainer = styled.tr(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  ", ";\n\n  ", "\n\n  ", "\n\n  ", "\n\n  ", "\n\n  &:hover > td {\n    opacity: 1;\n    ", "\n  }\n\n  &:hover > td > div {\n    opacity: 1;\n  }\n\n  ", ";\n"], ["\n  ", ";\n\n  ", "\n\n  ", "\n\n  ", "\n\n  ", "\n\n  &:hover > td {\n    opacity: 1;\n    ", "\n  }\n\n  &:hover > td > div {\n    opacity: 1;\n  }\n\n  ", ";\n"])), function (_a) {
    var isSelected = _a.isSelected;
    return isSelected && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      > td {\n        background-color: ", ";\n      }\n    "], ["\n      > td {\n        background-color: ", ";\n      }\n    "])), getColor('blue', 20));
}, function (_a) {
    var isClickable = _a.isClickable;
    return isClickable && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      &:hover {\n        cursor: pointer;\n      }\n    "], ["\n      &:hover {\n        cursor: pointer;\n      }\n    "])));
}, function (_a) {
    var isDragAndDroppable = _a.isDragAndDroppable;
    return isDragAndDroppable && css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n      & > *:first-child {\n        width: 44px;\n      }\n    "], ["\n      & > *:first-child {\n        width: 44px;\n      }\n    "])));
}, function (_a) {
    var placeholderPosition = _a.placeholderPosition;
    return placeholderPosition === 'top' && css(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n      background: linear-gradient(to bottom, ", " 4px, ", " 0px);\n    "], ["\n      background: linear-gradient(to bottom, ", " 4px, ", " 0px);\n    "])), getColor('blue', 40), getColor('white'));
}, function (_a) {
    var placeholderPosition = _a.placeholderPosition;
    return placeholderPosition === 'bottom' && css(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n      background: linear-gradient(to top, ", " 4px, ", " 0px);\n    "], ["\n      background: linear-gradient(to top, ", " 4px, ", " 0px);\n    "])), getColor('blue', 40), getColor('white'));
}, function (_a) {
    var isClickable = _a.isClickable;
    return isClickable && css(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n        background-color: ", ";\n      "], ["\n        background-color: ", ";\n      "])), getColor('grey', 20));
}, function (_a) {
    var level = _a.level;
    return level === 'warning'
        ? css(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n          > td {\n            :first-child {\n              padding: 0 0 0 5px;\n            }\n            background-color: ", ";\n          }\n        "], ["\n          > td {\n            :first-child {\n              padding: 0 0 0 5px;\n            }\n            background-color: ", ";\n          }\n        "])), getColor('yellow', 10)) : level === 'tertiary' && css(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n          > td {\n            background-color: ", ";\n            color: ", ";\n          }\n        "], ["\n          > td {\n            background-color: ", ";\n            color: ", ";\n          }\n        "])), getColor('grey', 20), getColor('grey', 120));
});
var CheckboxContainer = styled.td(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  background: none !important;\n  opacity: ", ";\n  cursor: auto;\n\n  > div {\n    justify-content: center;\n  }\n"], ["\n  background: none !important;\n  opacity: ", ";\n  cursor: auto;\n\n  > div {\n    justify-content: center;\n  }\n"])), function (_a) {
    var isVisible = _a.isVisible;
    return (isVisible ? 1 : 0);
});
var HandleCell = styled(TableCell)(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  cursor: grab;\n  width: 20px;\n\n  > div {\n    justify-content: center;\n  }\n\n  :active {\n    cursor: grabbing;\n  }\n"], ["\n  cursor: grab;\n  width: 20px;\n\n  > div {\n    justify-content: center;\n  }\n\n  :active {\n    cursor: grabbing;\n  }\n"])));
var getIcon = function (level) {
    switch (level) {
        case 'warning':
            return React.createElement(WarningIcon, null);
        case 'tertiary':
            return React.createElement(LockIcon, null);
    }
};
var WarningIcon = styled(DangerIcon)(templateObject_12 || (templateObject_12 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), getColor('yellow', 120));
var TableRow = forwardRef(function (_a, forwardedRef) {
    var _b = _a.rowIndex, rowIndex = _b === void 0 ? 0 : _b, _c = _a.isSelected, isSelected = _c === void 0 ? false : _c, level = _a.level, onSelectToggle = _a.onSelectToggle, onClick = _a.onClick, draggable = _a.draggable, onDragStart = _a.onDragStart, onDragEnd = _a.onDragEnd, children = _a.children, rest = __rest(_a, ["rowIndex", "isSelected", "level", "onSelectToggle", "onClick", "draggable", "onDragStart", "onDragEnd", "children"]);
    var _d = usePlaceholderPosition(rowIndex), placeholderPosition = _d[0], placeholderDragEnter = _d[1], placeholderDragLeave = _d[2], placeholderDragEnd = _d[3];
    var _e = useContext(TableContext), isSelectable = _e.isSelectable, displayCheckbox = _e.displayCheckbox, isDragAndDroppable = _e.isDragAndDroppable, hasWarningRows = _e.hasWarningRows, hasLockedRows = _e.hasLockedRows;
    if (isSelectable && (undefined === isSelected || undefined === onSelectToggle)) {
        throw Error('A row in a selectable table should have the prop "isSelected" and "onSelectToggle"');
    }
    var handleCheckboxChange = function (event) {
        event.stopPropagation();
        onSelectToggle === null || onSelectToggle === void 0 ? void 0 : onSelectToggle(!isSelected);
    };
    var handleDragEnter = function (event) {
        placeholderDragEnter(parseInt(event.dataTransfer.getData('text')));
    };
    var handleDragStart = function (event) {
        event.dataTransfer.setData('text', rowIndex.toString());
        onDragStart === null || onDragStart === void 0 ? void 0 : onDragStart(rowIndex);
    };
    var handleDragEnd = function () {
        placeholderDragEnd();
        onDragEnd === null || onDragEnd === void 0 ? void 0 : onDragEnd();
    };
    return (React.createElement(RowContainer, __assign({ ref: forwardedRef, isClickable: undefined !== onClick, isSelected: !!isSelected, level: level, isDragAndDroppable: isDragAndDroppable, onClick: onClick, placeholderPosition: isDragAndDroppable ? placeholderPosition : 'none', draggable: isDragAndDroppable && draggable, "data-draggable-index": rowIndex, onDragEnter: handleDragEnter, onDragLeave: placeholderDragLeave, onDragStart: handleDragStart, onDragEnd: handleDragEnd }, rest),
        isSelectable && (React.createElement(CheckboxContainer, { "aria-hidden": !displayCheckbox && !isSelected, isVisible: displayCheckbox || !!isSelected, onClick: handleCheckboxChange },
            React.createElement(Checkbox, { checked: isSelected, onChange: function (_value, e) {
                    handleCheckboxChange(e);
                } }))),
        isDragAndDroppable && (React.createElement(HandleCell, { onMouseDown: function () { return onDragStart === null || onDragStart === void 0 ? void 0 : onDragStart(rowIndex); }, onMouseUp: onDragEnd, "data-testid": "dragAndDrop" },
            React.createElement(RowIcon, { size: 16 }))),
        hasWarningRows && (React.createElement(TableCell, null, level === 'warning' && React.cloneElement(getIcon(level), { size: 16, 'data-testid': 'warning-icon' }))),
        children,
        hasLockedRows && (React.createElement(TableCell, null, level === 'tertiary' && React.cloneElement(getIcon(level), { size: 16, 'data-testid': 'lock-icon' })))));
});
export { TableRow };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12;
//# sourceMappingURL=TableRow.js.map
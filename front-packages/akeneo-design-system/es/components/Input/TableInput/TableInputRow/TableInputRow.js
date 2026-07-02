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
import styled, { css } from 'styled-components';
import React, { forwardRef, useContext } from 'react';
import { getColor } from '../../../../theme';
import { TableInputContext } from '../TableInputContext';
import { RowIcon } from '../../../../icons';
import { TableInputCell } from '../TableInputCell/TableInputCell';
import { usePlaceholderPosition } from '../../../../hooks/usePlaceholderPosition';
var getZebraBackgroundColor = function (highlighted, rowIndex) {
    return highlighted ? getColor('blue', 10) : rowIndex % 2 === 0 ? getColor('white') : getColor('grey', 20);
};
var TableInputTr = styled.tr(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  height: 40px;\n  & > td {\n    border: 1px solid ", ";\n    border-right-width: 0;\n    border-top-width: 0;\n    line-height: 39px;\n  }\n  & > td:first-child {\n    position: sticky;\n    left: 0;\n    margin-right: -1px;\n    z-index: 2;\n  }\n\n  ", "\n\n  & > td:last-child {\n    border-right-width: 1px;\n  }\n\n  ", "\n\n  ", "\n  \n  ", "\n    \n  ", "\n"], ["\n  height: 40px;\n  & > td {\n    border: 1px solid ", ";\n    border-right-width: 0;\n    border-top-width: 0;\n    line-height: 39px;\n  }\n  & > td:first-child {\n    position: sticky;\n    left: 0;\n    margin-right: -1px;\n    z-index: 2;\n  }\n\n  ", "\n\n  & > td:last-child {\n    border-right-width: 1px;\n  }\n\n  ", "\n\n  ", "\n  \n  ", "\n    \n  ", "\n"])), getColor('grey', 60), function (_a) {
    var isDragAndDroppable = _a.isDragAndDroppable;
    return isDragAndDroppable && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      & > td:nth-child(2) {\n        position: sticky;\n        left: 26px;\n        z-index: 1;\n        border-left: none;\n      }\n    "], ["\n      & > td:nth-child(2) {\n        position: sticky;\n        left: 26px;\n        z-index: 1;\n        border-left: none;\n      }\n    "])));
}, function (_a) {
    var placeholderPosition = _a.placeholderPosition, rowIndex = _a.rowIndex, highlighted = _a.highlighted;
    return placeholderPosition === 'bottom' && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      & > td {\n        background: linear-gradient(\n          to top,\n          ", " 4px,\n          ", " 0px\n        );\n      }\n    "], ["\n      & > td {\n        background: linear-gradient(\n          to top,\n          ", " 4px,\n          ", " 0px\n        );\n      }\n    "])), getColor('blue', 40), getZebraBackgroundColor(highlighted, rowIndex));
}, function (_a) {
    var placeholderPosition = _a.placeholderPosition, rowIndex = _a.rowIndex, highlighted = _a.highlighted;
    return placeholderPosition === 'top' && css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n      & > td {\n        background: linear-gradient(\n          to bottom,\n          ", " 4px,\n          ", " 0px\n        );\n      }\n    "], ["\n      & > td {\n        background: linear-gradient(\n          to bottom,\n          ", " 4px,\n          ", " 0px\n        );\n      }\n    "])), getColor('blue', 40), getZebraBackgroundColor(highlighted, rowIndex));
}, function (_a) {
    var placeholderPosition = _a.placeholderPosition, rowIndex = _a.rowIndex, highlighted = _a.highlighted;
    return placeholderPosition === 'none' && css(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n      & > td {\n        background: ", ";\n      }\n    "], ["\n      & > td {\n        background: ", ";\n      }\n    "])), getZebraBackgroundColor(highlighted, rowIndex));
}, function (_a) {
    var highlighted = _a.highlighted;
    return highlighted && css(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n      & > td {\n        &:before {\n          content: '';\n          border-bottom: 1px solid ", ";\n          position: relative;\n          width: 100%;\n          display: block;\n          height: 0;\n          margin-top: -1px;\n        }\n        border-bottom-color: ", ";\n        border-left-color: ", ";\n        &:last-child {\n          border-right-color: ", ";\n        }\n      }\n    "], ["\n      & > td {\n        &:before {\n          content: '';\n          border-bottom: 1px solid ", ";\n          position: relative;\n          width: 100%;\n          display: block;\n          height: 0;\n          margin-top: -1px;\n        }\n        border-bottom-color: ", ";\n        border-left-color: ", ";\n        &:last-child {\n          border-right-color: ", ";\n        }\n      }\n    "])), getColor('blue', 100), getColor('blue', 100), getColor('blue', 100), getColor('blue', 100));
});
var DragAndDropCell = styled(TableInputCell)(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  max-width: 26px;\n  min-width: 26px;\n  width: 26px;\n  color: ", ";\n  text-align: right;\n  cursor: grab;\n  vertical-align: middle;\n  line-height: 0px !important;\n  & > div {\n    height: 39px;\n    vertical-align: middle;\n    line-height: 44px;\n  }\n"], ["\n  max-width: 26px;\n  min-width: 26px;\n  width: 26px;\n  color: ", ";\n  text-align: right;\n  cursor: grab;\n  vertical-align: middle;\n  line-height: 0px !important;\n  & > div {\n    height: 39px;\n    vertical-align: middle;\n    line-height: 44px;\n  }\n"])), getColor('grey', 100));
var TableInputRow = forwardRef(function (_a, forwardedRef) {
    var children = _a.children, _b = _a.rowIndex, rowIndex = _b === void 0 ? 0 : _b, draggable = _a.draggable, _c = _a.highlighted, highlighted = _c === void 0 ? false : _c, onDragStart = _a.onDragStart, onDragEnd = _a.onDragEnd, rest = __rest(_a, ["children", "rowIndex", "draggable", "highlighted", "onDragStart", "onDragEnd"]);
    var _d = usePlaceholderPosition(rowIndex), placeholderPosition = _d[0], placeholderDragEnter = _d[1], placeholderDragLeave = _d[2], placeholderDragEnd = _d[3];
    var isDragAndDroppable = useContext(TableInputContext).isDragAndDroppable;
    var handleDragEnter = function (event) {
        if (isDragAndDroppable) {
            placeholderDragEnter(parseInt(event.dataTransfer.getData('text')));
        }
    };
    var handleDragStart = function (event) {
        if (isDragAndDroppable) {
            event.dataTransfer.setData('text', rowIndex.toString());
            onDragStart === null || onDragStart === void 0 ? void 0 : onDragStart(rowIndex);
        }
    };
    var handleDragEnd = function () {
        if (isDragAndDroppable) {
            placeholderDragEnd();
            onDragEnd === null || onDragEnd === void 0 ? void 0 : onDragEnd();
        }
    };
    return (React.createElement(TableInputTr, __assign({ highlighted: highlighted, draggable: isDragAndDroppable && draggable, isDragAndDroppable: isDragAndDroppable, "data-draggable-index": rowIndex, onDragEnter: handleDragEnter, onDragLeave: placeholderDragLeave, onDragStart: handleDragStart, onDragEnd: handleDragEnd, ref: forwardedRef, placeholderPosition: placeholderPosition, rowIndex: rowIndex }, rest),
        isDragAndDroppable && (React.createElement(DragAndDropCell, { onMouseDown: function () { return onDragStart === null || onDragStart === void 0 ? void 0 : onDragStart(rowIndex); }, onMouseUp: onDragEnd, "data-testid": "dragAndDrop" },
            React.createElement("div", null,
                React.createElement(RowIcon, { size: 16 })))),
        children));
});
TableInputRow.displayName = 'TableInput.Row';
export { TableInputRow };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7;
//# sourceMappingURL=TableInputRow.js.map
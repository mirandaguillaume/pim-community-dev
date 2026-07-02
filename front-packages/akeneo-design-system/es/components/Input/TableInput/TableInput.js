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
import styled, { css } from 'styled-components';
import { TableInputHeader } from './TableInputHeader/TableInputHeader';
import { TableInputHeaderCell } from './TableInputHeaderCell/TableInputHeaderCell';
import { TableInputBody } from './TableInputBody/TableInputBody';
import { TableInputCell } from './TableInputCell/TableInputCell';
import { TableInputRow } from './TableInputRow/TableInputRow';
import { TableInputText } from './TableInputText/TableInputText';
import { TableInputNumber } from './TableInputNumber/TableInputNumber';
import { TableInputBoolean } from './TableInputBoolean/TableInputBoolean';
import { TableInputSelect } from './TableInputSelect/TableInputSelect';
import { TableInputContext } from './TableInputContext';
import { TableInputCellContent } from './TableInputCellContent/TableInputCellContent';
import { TableInputMeasurement } from './TableInputMeasurement/TableInputMeasurement';
var TableInputContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  width: 100%;\n  overflow: auto;\n"], ["\n  width: 100%;\n  overflow: auto;\n"])));
var TableInputTable = styled.table(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  border-spacing: 0;\n  width: 100%;\n\n  & th:first-child {\n    transition: box-shadow 0.15s;\n  }\n  &.shadowed th:first-child {\n    box-shadow: rgba(0, 0, 0, 0.2) 0px 7.5px 15px 0px;\n  }\n\n  ", "\n"], ["\n  border-spacing: 0;\n  width: 100%;\n\n  & th:first-child {\n    transition: box-shadow 0.15s;\n  }\n  &.shadowed th:first-child {\n    box-shadow: rgba(0, 0, 0, 0.2) 0px 7.5px 15px 0px;\n  }\n\n  ", "\n"])), function (_a) {
    var isDragAndDroppable = _a.isDragAndDroppable;
    return !isDragAndDroppable
        ? css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n          & tr > td:first-child {\n            transition: box-shadow 0.15s;\n          }\n          &.shadowed tr > td:first-child {\n            box-shadow: rgba(0, 0, 0, 0.2) 0px 15px 15px 0px;\n          }\n        "], ["\n          & tr > td:first-child {\n            transition: box-shadow 0.15s;\n          }\n          &.shadowed tr > td:first-child {\n            box-shadow: rgba(0, 0, 0, 0.2) 0px 15px 15px 0px;\n          }\n        "]))) : css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n          & tr > td:nth-child(2) {\n            transition: box-shadow 0.15s;\n          }\n          &.shadowed tr > td:nth-child(2) {\n            box-shadow: rgba(0, 0, 0, 0.2) 0px 15px 15px 0px;\n          }\n        "], ["\n          & tr > td:nth-child(2) {\n            transition: box-shadow 0.15s;\n          }\n          &.shadowed tr > td:nth-child(2) {\n            box-shadow: rgba(0, 0, 0, 0.2) 0px 15px 15px 0px;\n          }\n        "])));
});
var TableInput = function (_a) {
    var children = _a.children, _b = _a.readOnly, readOnly = _b === void 0 ? false : _b, _c = _a.isDragAndDroppable, isDragAndDroppable = _c === void 0 ? false : _c, onReorder = _a.onReorder, rest = __rest(_a, ["children", "readOnly", "isDragAndDroppable", "onReorder"]);
    var _d = React.useState(false), shadowed = _d[0], setShadowed = _d[1];
    var handleScroll = function (event) {
        setShadowed(event.currentTarget.scrollLeft > 0);
    };
    return (React.createElement(TableInputContext.Provider, { value: { readOnly: readOnly, isDragAndDroppable: isDragAndDroppable, onReorder: onReorder } },
        React.createElement(TableInputContainer, __assign({ onScroll: handleScroll }, rest),
            React.createElement(TableInputTable, { className: shadowed ? 'shadowed' : '', isDragAndDroppable: isDragAndDroppable }, children))));
};
TableInput.Header = TableInputHeader;
TableInput.HeaderCell = TableInputHeaderCell;
TableInput.Body = TableInputBody;
TableInput.Row = TableInputRow;
TableInput.Cell = TableInputCell;
TableInput.CellContent = TableInputCellContent;
TableInput.Text = TableInputText;
TableInput.Number = TableInputNumber;
TableInput.Boolean = TableInputBoolean;
TableInput.Select = TableInputSelect;
TableInput.Measurement = TableInputMeasurement;
export { TableInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=TableInput.js.map
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
import React, { useMemo } from 'react';
import styled from 'styled-components';
import { TableCell } from './TableCell/TableCell';
import { TableHeader } from './TableHeader/TableHeader';
import { TableHeaderCell } from './TableHeaderCell/TableHeaderCell';
import { TableActionCell } from './TableActionCell/TableActionCell';
import { TableRow } from './TableRow/TableRow';
import { TableContext } from './TableContext';
import { TableBody } from './TableBody/TableBody';
var TableContainer = styled.table(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  border-collapse: collapse;\n  width: 100%;\n"], ["\n  border-collapse: collapse;\n  width: 100%;\n"])));
var Table = function (_a) {
    var _b = _a.isSelectable, isSelectable = _b === void 0 ? false : _b, _c = _a.hasWarningRows, hasWarningRows = _c === void 0 ? false : _c, _d = _a.hasLockedRows, hasLockedRows = _d === void 0 ? false : _d, _e = _a.displayCheckbox, displayCheckbox = _e === void 0 ? false : _e, _f = _a.isDragAndDroppable, isDragAndDroppable = _f === void 0 ? false : _f, _g = _a.onReorder, onReorder = _g === void 0 ? undefined : _g, children = _a.children, rest = __rest(_a, ["isSelectable", "hasWarningRows", "hasLockedRows", "displayCheckbox", "isDragAndDroppable", "onReorder", "children"]);
    var providerValue = useMemo(function () { return ({ isSelectable: isSelectable, hasWarningRows: hasWarningRows, hasLockedRows: hasLockedRows, displayCheckbox: displayCheckbox, isDragAndDroppable: isDragAndDroppable, onReorder: onReorder }); }, [isSelectable, hasWarningRows, hasLockedRows, displayCheckbox, isDragAndDroppable, onReorder]);
    return (React.createElement(TableContext.Provider, { value: providerValue },
        React.createElement(TableContainer, __assign({}, rest), children)));
};
TableHeader.displayName = 'Table.Header';
TableHeaderCell.displayName = 'Table.HeaderCell';
TableBody.displayName = 'Table.Body';
TableRow.displayName = 'Table.Row';
TableCell.displayName = 'Table.Cell';
TableActionCell.displayName = 'Table.ActionCell';
Table.Header = TableHeader;
Table.HeaderCell = TableHeaderCell;
Table.Body = TableBody;
Table.Row = TableRow;
Table.Cell = TableCell;
Table.ActionCell = TableActionCell;
export { Table };
var templateObject_1;
//# sourceMappingURL=Table.js.map
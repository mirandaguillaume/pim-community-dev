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
import React, { Children, cloneElement, useContext } from 'react';
import { TableInputContext } from '../TableInputContext';
import { useDragElementIndex } from '../../../../hooks/useDragElementIndex';
import { useDrop } from '../../../../hooks/useDrop';
var TableInputBody = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var _b = useDragElementIndex(), draggedElementIndex = _b[0], onDragStart = _b[1], onDragEnd = _b[2];
    var _c = useContext(TableInputContext), isDragAndDroppable = _c.isDragAndDroppable, onReorder = _c.onReorder;
    var decoratedChildren = Children.map(children, function (child, rowIndex) {
        if (!React.isValidElement(child)) {
            return null;
        }
        return isDragAndDroppable
            ? cloneElement(child, {
                rowIndex: rowIndex,
                draggable: rowIndex === draggedElementIndex,
                onDragStart: onDragStart,
                onDragEnd: onDragEnd,
            })
            : cloneElement(child, {
                rowIndex: rowIndex,
            });
    });
    var rowCount = Children.count(decoratedChildren);
    var _d = useDrop(rowCount, draggedElementIndex, onReorder), tableId = _d[0], onDrop = _d[1], onDragOver = _d[2];
    return (React.createElement("tbody", __assign({ "data-table-id": tableId, onDrop: onDrop, onDragOver: onDragOver, ref: forwardedRef }, rest), decoratedChildren));
});
TableInputBody.displayName = 'TableInput.Body';
export { TableInputBody };
//# sourceMappingURL=TableInputBody.js.map
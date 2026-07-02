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
import React, { cloneElement, Children, useContext } from 'react';
import { TableContext } from '../TableContext';
import { useDrop } from '../../../hooks/useDrop';
import { useDragElementIndex } from '../../../hooks/useDragElementIndex';
var TableBody = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var _b = useDragElementIndex(), draggedElementIndex = _b[0], onDragStart = _b[1], onDragEnd = _b[2];
    var _c = useContext(TableContext), isDragAndDroppable = _c.isDragAndDroppable, onReorder = _c.onReorder;
    var decoratedChildren = isDragAndDroppable
        ? Children.map(children, function (child, rowIndex) {
            if (!React.isValidElement(child)) {
                return null;
            }
            return cloneElement(child, {
                rowIndex: rowIndex,
                draggable: rowIndex === draggedElementIndex,
                onDragStart: onDragStart,
                onDragEnd: onDragEnd,
            });
        })
        : children;
    var rowCount = Children.count(children);
    var _d = useDrop(rowCount, draggedElementIndex, onReorder), tableId = _d[0], onDrop = _d[1], onDragOver = _d[2];
    return (React.createElement("tbody", __assign({ ref: forwardedRef, "data-table-id": tableId, onDrop: onDrop, onDragOver: onDragOver }, rest), decoratedChildren));
});
export { TableBody };
//# sourceMappingURL=TableBody.js.map
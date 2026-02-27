"use strict";
var __read = (this && this.__read) || function (o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.useDropTreeNode = void 0;
var react_1 = require("react");
var OrderableTreeProvider_1 = require("../components/providers/OrderableTreeProvider");
var useDropTreeNode = function (node, reorder) {
    var _a = (0, react_1.useContext)(OrderableTreeProvider_1.OrderableTreeContext), draggedNode = _a.draggedNode, setDraggedNode = _a.setDraggedNode;
    var _b = __read((0, react_1.useState)('none'), 2), placeholderPosition = _b[0], setPlaceholderPosition = _b[1];
    var _c = __read((0, react_1.useState)(0), 2), overingCount = _c[0], setOveringCount = _c[1];
    var _d = __read((0, react_1.useState)(null), 2), dropTarget = _d[0], setDropTarget = _d[1];
    var onDrop = (0, react_1.useCallback)(function () {
        if (!draggedNode || !dropTarget) {
            return;
        }
        if (!node || node.parentId === null || node.type === 'root') {
            return;
        }
        reorder(draggedNode.identifier, dropTarget, function () {
            setOveringCount(0);
            setDropTarget(null);
            setDraggedNode(null);
        });
    }, [draggedNode, dropTarget, node, reorder, setDraggedNode]);
    var onDragOver = (0, react_1.useCallback)(function (target, cursorPosition) {
        if (!node || !draggedNode) {
            return;
        }
        if (draggedNode.identifier === node.identifier) {
            return;
        }
        if (node.parentId === null || node.type === 'root') {
            return;
        }
        var hoveredCategoryDimensions = target.getBoundingClientRect();
        var topTierHeight = (hoveredCategoryDimensions.bottom - hoveredCategoryDimensions.top) / 3;
        var bottomTierHeight = topTierHeight * 2;
        var cursorRelativePosition = cursorPosition.y - hoveredCategoryDimensions.top;
        var newDropTarget = {
            parentId: node.parentId,
            identifier: node.identifier,
            position: cursorRelativePosition < topTierHeight
                ? 'before'
                : cursorRelativePosition < bottomTierHeight
                    ? 'in'
                    : 'after',
        };
        if (!dropTarget || JSON.stringify(dropTarget) !== JSON.stringify(newDropTarget)) {
            setDropTarget(newDropTarget);
        }
    }, [node, draggedNode, dropTarget]);
    var onDragEnd = (0, react_1.useCallback)(function () {
        setOveringCount(0);
        setDropTarget(null);
        setDraggedNode(null);
    }, [setDraggedNode]);
    var onDragEnter = (0, react_1.useCallback)(function () {
        setOveringCount(function (count) { return count + 1; });
    }, []);
    var onDragLeave = (0, react_1.useCallback)(function () {
        setOveringCount(function (count) { return count - 1; });
    }, []);
    (0, react_1.useEffect)(function () {
        if (overingCount === 0 || !dropTarget || !node || dropTarget.identifier !== node.identifier) {
            setPlaceholderPosition('none');
            return;
        }
        var position = 'none';
        switch (dropTarget.position) {
            case 'after':
                position = 'bottom';
                break;
            case 'before':
                position = 'top';
                break;
            case 'in':
                position = 'middle';
                break;
        }
        setPlaceholderPosition(position);
    }, [node, dropTarget, overingCount]);
    return {
        dropTarget: dropTarget,
        onDrop: onDrop,
        onDragOver: onDragOver,
        placeholderPosition: placeholderPosition,
        onDragEnd: onDragEnd,
        onDragEnter: onDragEnter,
        onDragLeave: onDragLeave,
    };
};
exports.useDropTreeNode = useDropTreeNode;
//# sourceMappingURL=useDropTreeNode.js.map
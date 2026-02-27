"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useDragTreeNode = void 0;
var react_1 = require("react");
var OrderableTreeProvider_1 = require("../components/providers/OrderableTreeProvider");
var useDragTreeNode = function (node, index) {
    var _a = (0, react_1.useContext)(OrderableTreeProvider_1.OrderableTreeContext), draggedNode = _a.draggedNode, setDraggedNode = _a.setDraggedNode, isActive = _a.isActive;
    var isDraggable = (0, react_1.useMemo)(function () { return isActive && node && node.type !== 'root'; }, [isActive, node]);
    var isDragged = (0, react_1.useCallback)(function () {
        return draggedNode !== null && node !== undefined && draggedNode.identifier === node.identifier;
    }, [draggedNode, node]);
    var onDragStart = (0, react_1.useCallback)(function () {
        if (!node) {
            return;
        }
        if (node.parentId === null) {
            throw new Error("Impossible to drag the node \"" + node.identifier + "\", parentId is missing");
        }
        setDraggedNode({
            identifier: node.identifier,
            parentId: node.parentId,
            position: index,
        });
    }, [node, index, setDraggedNode]);
    return {
        draggedNode: draggedNode,
        isDraggable: isDraggable,
        isDragged: isDragged,
        onDragStart: onDragStart,
    };
};
exports.useDragTreeNode = useDragTreeNode;
//# sourceMappingURL=useDragTreeNode.js.map
import { useCallback, useState } from 'react';
var useDragElementIndex = function () {
    var _a = useState(null), draggedElementIndex = _a[0], setDraggedElementIndex = _a[1];
    var onDragStart = useCallback(function (index) { return setDraggedElementIndex(index); }, []);
    var onDragEnd = useCallback(function () { return setDraggedElementIndex(null); }, []);
    return [draggedElementIndex, onDragStart, onDragEnd];
};
export { useDragElementIndex };
//# sourceMappingURL=useDragElementIndex.js.map
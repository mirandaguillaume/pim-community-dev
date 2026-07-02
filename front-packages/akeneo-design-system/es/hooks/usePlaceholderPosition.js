import { useCallback, useEffect, useState } from 'react';
var usePlaceholderPosition = function (rowIndex) {
    var _a = useState(0), overingCount = _a[0], setOveringCount = _a[1];
    var _b = useState('none'), placeholderPosition = _b[0], setPlaceholderPosition = _b[1];
    useEffect(function () {
        setOveringCount(0);
    }, [rowIndex]);
    var dragEnter = useCallback(function (draggedElementIndex) {
        setOveringCount(function (count) { return count + 1; });
        setPlaceholderPosition(draggedElementIndex >= rowIndex ? 'top' : 'bottom');
    }, [rowIndex]);
    var dragLeave = useCallback(function () {
        setOveringCount(function (count) { return count - 1; });
    }, []);
    var dragEnd = useCallback(function () {
        setOveringCount(0);
    }, []);
    return [overingCount === 0 ? 'none' : placeholderPosition, dragEnter, dragLeave, dragEnd];
};
export { usePlaceholderPosition };
//# sourceMappingURL=usePlaceholderPosition.js.map
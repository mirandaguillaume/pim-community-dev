import { useState, useEffect } from 'react';
var useVerticalPosition = function (ref, forcedPosition) {
    var _a = useState(forcedPosition), verticalPosition = _a[0], setVerticalPosition = _a[1];
    useEffect(function () {
        if (null !== ref.current && undefined === forcedPosition) {
            var _a = ref.current.getBoundingClientRect(), elementHeight = _a.height, distanceToTop = _a.top;
            var windowHeight = window.innerHeight || document.documentElement.clientHeight;
            var distanceToBottom = windowHeight - (elementHeight + distanceToTop);
            var elementIsOverlappingBottom = distanceToBottom < 0;
            var elementIsOverlappingTop = distanceToTop < 0;
            setVerticalPosition(elementIsOverlappingBottom ? (elementIsOverlappingTop ? 'down' : 'up') : 'down');
        }
    }, [forcedPosition, ref.current]);
    return verticalPosition;
};
var useHorizontalPosition = function (ref, forcedPosition) {
    var _a = useState(forcedPosition), horizontalPosition = _a[0], setHorizontalPosition = _a[1];
    useEffect(function () {
        if (null !== ref.current && undefined === forcedPosition) {
            var _a = ref.current.getBoundingClientRect(), elementWidth = _a.width, distanceToLeft = _a.left;
            var windowWidth = window.innerWidth;
            var distanceToRight = windowWidth - (elementWidth + distanceToLeft);
            setHorizontalPosition(distanceToLeft > distanceToRight ? 'left' : 'right');
        }
    }, [forcedPosition, ref.current]);
    return horizontalPosition;
};
export { useVerticalPosition, useHorizontalPosition };
//# sourceMappingURL=usePosition.js.map
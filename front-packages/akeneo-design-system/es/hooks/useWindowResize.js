import { useState, useEffect } from 'react';
var useWindowResize = function () {
    var _a = useState({ width: window.innerWidth, height: window.innerHeight }), windowSize = _a[0], setWindowSize = _a[1];
    useEffect(function () {
        var onResize = function () { return setWindowSize({ width: window.innerWidth, height: window.innerHeight }); };
        window.addEventListener('resize', onResize);
        return function () { return window.removeEventListener('resize', onResize); };
    });
    return windowSize;
};
export { useWindowResize };
//# sourceMappingURL=useWindowResize.js.map
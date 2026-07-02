import { useEffect, useState } from 'react';
var isDocumentVisible = function () { return 'hidden' !== document.visibilityState; };
var useDocumentVisibility = function () {
    var _a = useState(isDocumentVisible()), isVisible = _a[0], setVisible = _a[1];
    var handleVisibilityChange = function () { return setVisible(isDocumentVisible()); };
    useEffect(function () {
        window.addEventListener('visibilitychange', handleVisibilityChange);
        return function () {
            window.removeEventListener('visibilitychange', handleVisibilityChange);
        };
    }, []);
    return isVisible;
};
export { useDocumentVisibility };
//# sourceMappingURL=useDocumentVisibility.js.map
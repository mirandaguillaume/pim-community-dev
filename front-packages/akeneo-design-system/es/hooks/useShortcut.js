import { useCallback, useEffect, useRef } from 'react';
var useShortcut = function (key, callback, externalRef) {
    if (externalRef === void 0) { externalRef = null; }
    var internalRef = useRef(null);
    var ref = null === externalRef ? internalRef : externalRef;
    var memoizedCallback = useCallback(function (event) {
        if (key === event.key) {
            callback(event);
            return true;
        }
        return false;
    }, [key, callback, ref]);
    useEffect(function () {
        if (typeof ref !== 'function' && null !== ref.current) {
            var element_1 = ref.current;
            element_1.addEventListener('keydown', memoizedCallback);
            return function () { return element_1.removeEventListener('keydown', memoizedCallback); };
        }
        else {
            window.addEventListener('keydown', memoizedCallback);
            return function () { return window.removeEventListener('keydown', memoizedCallback); };
        }
    }, [memoizedCallback, ref]);
    return ref;
};
export { useShortcut };
//# sourceMappingURL=useShortcut.js.map
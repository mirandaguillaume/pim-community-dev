import { useCallback, useEffect } from 'react';
var useAutoFocus = function (ref) {
    var focus = useCallback(function () {
        setTimeout(function () {
            if (ref.current !== null)
                ref.current.focus();
        }, 0);
    }, [ref]);
    useEffect(focus, []);
    return focus;
};
export { useAutoFocus };
//# sourceMappingURL=useAutoFocus.js.map
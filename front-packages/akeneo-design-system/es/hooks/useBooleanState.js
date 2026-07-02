import { useCallback, useState } from 'react';
var useBooleanState = function (defaultValue) {
    if (defaultValue === void 0) { defaultValue = false; }
    var _a = useState(defaultValue), value = _a[0], setValue = _a[1];
    var setTrue = useCallback(function () { return setValue(true); }, []);
    var setFalse = useCallback(function () { return setValue(false); }, []);
    return [value, setTrue, setFalse];
};
export { useBooleanState };
//# sourceMappingURL=useBooleanState.js.map
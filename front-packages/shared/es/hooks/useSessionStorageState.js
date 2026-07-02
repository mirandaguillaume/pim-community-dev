import { useState, useEffect } from 'react';
var useSessionStorageState = function (defaultValue, key) {
    var storageValue = sessionStorage.getItem(key);
    var _a = useState(null !== storageValue ? JSON.parse(storageValue) : defaultValue), value = _a[0], setValue = _a[1];
    useEffect(function () {
        sessionStorage.setItem(key, JSON.stringify(value));
    }, [value]);
    return [value, setValue];
};
export { useSessionStorageState };
//# sourceMappingURL=useSessionStorageState.js.map
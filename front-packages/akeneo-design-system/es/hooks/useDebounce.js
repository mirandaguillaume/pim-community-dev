import { useEffect, useState } from 'react';
var useDebounce = function (value, delay) {
    if (delay === void 0) { delay = 300; }
    var _a = useState(value), debouncedValue = _a[0], setDebouncedValue = _a[1];
    useEffect(function () {
        var timer = setTimeout(function () {
            setDebouncedValue(value);
        }, delay);
        return function () {
            clearTimeout(timer);
        };
    }, [value, delay]);
    return debouncedValue;
};
export { useDebounce };
//# sourceMappingURL=useDebounce.js.map
var _this = this;
import { useCallback, useEffect, useState } from 'react';
var debounceCallback = function (callback, delay) {
    var timer;
    return function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        var context = _this;
        clearTimeout(timer);
        timer = window.setTimeout(function () {
            callback.apply(context, args);
        }, delay);
    };
};
var useDebounce = function (value, delay) {
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
var useDebounceCallback = function (callback, delay) {
    return useCallback(debounceCallback(callback, delay), [callback, delay]);
};
export { useDebounce, useDebounceCallback };
//# sourceMappingURL=useDebounce.js.map
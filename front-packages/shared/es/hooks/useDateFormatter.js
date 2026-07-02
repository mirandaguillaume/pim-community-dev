var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
import { useCallback } from 'react';
import { useUserContext } from './useUserContext';
var useDateFormatter = function () {
    var _a, _b;
    var user = useUserContext();
    var locale = (_b = (_a = user.get('uiLocale')) === null || _a === void 0 ? void 0 : _a.replace('_', '-')) !== null && _b !== void 0 ? _b : 'en-US';
    var timeZone = user.get('timezone');
    return useCallback(function (date, options) {
        options = __assign({ timeZone: timeZone }, options);
        try {
            return new Intl.DateTimeFormat(locale, options).format(new Date(date));
        }
        catch (error) {
            if (error instanceof RangeError) {
                return new Intl.DateTimeFormat(locale, __assign(__assign({}, options), { timeZone: 'UTC', timeZoneName: 'short' })).format(new Date(date));
            }
            throw error;
        }
    }, [locale, timeZone]);
};
export { useDateFormatter };
//# sourceMappingURL=useDateFormatter.js.map
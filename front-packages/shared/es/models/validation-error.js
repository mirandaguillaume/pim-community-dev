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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
var filterErrors = function (errors, propertyPath) {
    return errors
        .filter(function (error) { return error.propertyPath.startsWith(propertyPath); })
        .map(function (error) { return (__assign(__assign({}, error), { propertyPath: error.propertyPath.replace(propertyPath, '') })); });
};
var getErrorsForPath = function (errors, propertyPath) {
    return errors.filter(function (error) { return error.propertyPath === propertyPath; });
};
var formatParameters = function (errors) {
    return errors.map(function (error) { return (__assign(__assign({}, error), { parameters: Object.keys(error.parameters).reduce(function (result, key) {
            var _a;
            return (__assign(__assign({}, result), (_a = {}, _a[key.replace('{{ ', '').replace(' }}', '')] = error.parameters[key], _a)));
        }, {}) })); });
};
var partition = function (items, condition) {
    return items.reduce(function (result, item) {
        result[condition(item) ? 0 : 1].push(item);
        return result;
    }, [[], []]);
};
var partitionErrors = function (errors, conditions) {
    var results = [];
    var restErrors = __spreadArray([], errors, true);
    conditions.forEach(function (condition) {
        var _a = partition(restErrors, condition), match = _a[0], rest = _a[1];
        results.push(match);
        restErrors = rest;
    });
    return __spreadArray(__spreadArray([], results, true), [restErrors], false);
};
export { filterErrors, getErrorsForPath, partitionErrors, formatParameters };
//# sourceMappingURL=validation-error.js.map
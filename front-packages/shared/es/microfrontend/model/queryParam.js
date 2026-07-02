var createQueryParam = function (parameters) {
    if (!parameters)
        return '';
    var queryParameters = Object.entries(parameters).map(function (_a) {
        var key = _a[0], val = _a[1];
        if (Array.isArray(val)) {
            return val.map(function (value) { return "".concat(key, "[]=").concat(value); }).join('&');
        }
        return "".concat(key, "=").concat(val);
    });
    return queryParameters.length > 0 ? '?' + queryParameters.join('&') : '';
};
export { createQueryParam };
//# sourceMappingURL=queryParam.js.map
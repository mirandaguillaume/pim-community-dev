"use strict";
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
Object.defineProperty(exports, "__esModule", { value: true });
exports.useForm = void 0;
var react_1 = require("react");
var useForm = function (defaultValues) {
    var _a = (0, react_1.useState)(defaultValues), state = _a[0], setState = _a[1];
    var setValue = (0, react_1.useCallback)(function (path, value) {
        var _a;
        if (!(path in state)) {
            throw Error("The field " + path + " does not belong to this form.");
        }
        setState(__assign(__assign({}, state), (_a = {}, _a[path] = value, _a)));
    }, [state, setState]);
    var clear = (0, react_1.useCallback)(function () {
        setState(defaultValues);
    }, [setState, defaultValues]);
    return [state, setValue, clear];
};
exports.useForm = useForm;
//# sourceMappingURL=use-form.js.map
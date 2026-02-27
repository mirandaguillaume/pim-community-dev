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
var __read = (this && this.__read) || function (o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Status = exports.SaveStatusProvider = exports.SaveStatusContext = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var Status;
(function (Status) {
    Status[Status["SAVED"] = 0] = "SAVED";
    Status[Status["ERRORS"] = 1] = "ERRORS";
    Status[Status["SAVING"] = 2] = "SAVING";
    Status[Status["EDITING"] = 3] = "EDITING";
})(Status || (Status = {}));
exports.Status = Status;
var SaveStatusContext = (0, react_1.createContext)(null);
exports.SaveStatusContext = SaveStatusContext;
var resolveGlobalStatus = function (statusList) {
    var globalStatus = Status.SAVED;
    if (Object.keys(statusList).length > 0) {
        globalStatus = Object.values(statusList).reduce(function (previousGlobalStatus, currentFieldStatus) {
            return Math.max(Number(previousGlobalStatus), Number(currentFieldStatus));
        });
    }
    return globalStatus;
};
var SaveStatusProvider = function (_a) {
    var children = _a.children;
    var _b = __read((0, react_1.useState)({}), 2), setStatusList = _b[1];
    var _c = __read((0, react_1.useState)(Status.SAVED), 2), globalStatus = _c[0], setGlobalStatus = _c[1];
    var handleStatusListChange = function (id, status) {
        setStatusList(function (previousStatusList) {
            var _a;
            var statusList = __assign(__assign({}, previousStatusList), (_a = {}, _a[id] = status, _a));
            setGlobalStatus(function (previousGlobalStatus) {
                var globalStatus = resolveGlobalStatus(statusList);
                if (previousGlobalStatus !== globalStatus) {
                    console.debug('Save status:', Status[globalStatus], statusList);
                }
                return globalStatus;
            });
            return statusList;
        });
    };
    return ((0, jsx_runtime_1.jsx)(SaveStatusContext.Provider, __assign({ value: { globalStatus: globalStatus, handleStatusListChange: handleStatusListChange } }, { children: children }), void 0));
};
exports.SaveStatusProvider = SaveStatusProvider;
//# sourceMappingURL=SaveStatusProvider.js.map
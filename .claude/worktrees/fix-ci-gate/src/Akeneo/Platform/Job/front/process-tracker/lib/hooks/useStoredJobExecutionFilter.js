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
exports.useStoredJobExecutionFilter = void 0;
var react_1 = require("react");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../models");
var FILTER_LOCAL_STORAGE_KEY = 'process-tracker.filters';
var useStoredJobExecutionFilter = function () {
    var _a = (0, shared_1.useStorageState)((0, models_1.getDefaultJobExecutionFilter)(), FILTER_LOCAL_STORAGE_KEY), jobExecutionFilter = _a[0], setJobExecutionFilter = _a[1];
    var sanitizedJobExecutionFilter = (0, react_1.useMemo)(function () {
        return __assign(__assign({}, (0, models_1.getDefaultJobExecutionFilter)()), jobExecutionFilter);
    }, [jobExecutionFilter]);
    return [sanitizedJobExecutionFilter, setJobExecutionFilter];
};
exports.useStoredJobExecutionFilter = useStoredJobExecutionFilter;
//# sourceMappingURL=useStoredJobExecutionFilter.js.map
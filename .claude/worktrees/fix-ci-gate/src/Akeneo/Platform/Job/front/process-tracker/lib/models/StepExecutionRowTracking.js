"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getStepExecutionRowTrackingPercent = exports.getStepExecutionRowTrackingLevel = void 0;
var JobStatus_1 = require("./JobStatus");
var getStepExecutionRowTrackingLevel = function (_a) {
    var warning_count = _a.warning_count, has_error = _a.has_error, status = _a.status;
    if (has_error)
        return 'danger';
    if (0 < warning_count)
        return 'warning';
    if ((0, JobStatus_1.isPaused)(status))
        return 'tertiary';
    return 'primary';
};
exports.getStepExecutionRowTrackingLevel = getStepExecutionRowTrackingLevel;
var getStepExecutionRowTrackingPercent = function (step) {
    if (step.status === 'STARTING')
        return 0;
    if (step.status === 'COMPLETED')
        return 100;
    if (step.total_items === 0 || !step.is_trackable) {
        switch (step.status) {
            case 'STOPPED':
            case 'FAILED':
            case 'ABANDONED':
                return 100;
            case 'IN_PROGRESS':
            case 'STOPPING':
            case 'UNKNOWN':
            default:
                return 'indeterminate';
        }
    }
    return (step.processed_items * 100) / step.total_items;
};
exports.getStepExecutionRowTrackingPercent = getStepExecutionRowTrackingPercent;
//# sourceMappingURL=StepExecutionRowTracking.js.map
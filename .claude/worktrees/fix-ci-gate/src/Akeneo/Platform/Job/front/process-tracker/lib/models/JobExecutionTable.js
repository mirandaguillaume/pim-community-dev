"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.getJobExecutionRowTrackingProgressLabel = exports.canShowJobExecutionDetail = exports.jobCanBeStopped = void 0;
var intl_duration_1 = require("../tools/intl-duration");
var STOPPABLE_STATUS = ['STARTING', 'IN_PROGRESS'];
var jobCanBeStopped = function (jobExecutionRow) {
    return jobExecutionRow.is_stoppable && STOPPABLE_STATUS.includes(jobExecutionRow.status);
};
exports.jobCanBeStopped = jobCanBeStopped;
var JOB_TYPES_WITH_ACL = {
    import: 'pim_importexport_import_execution_show',
    export: 'pim_importexport_export_execution_show',
};
var canShowJobExecutionDetail = function (isGranted, jobExecutionRow) {
    return jobExecutionRow.type in JOB_TYPES_WITH_ACL ? isGranted(JOB_TYPES_WITH_ACL[jobExecutionRow.type]) : true;
};
exports.canShowJobExecutionDetail = canShowJobExecutionDetail;
var getJobExecutionRowTrackingProgressLabel = function (translate, jobExecutionRow) {
    var _a;
    var step = (_a = jobExecutionRow.tracking.steps[jobExecutionRow.tracking.current_step - 1]) !== null && _a !== void 0 ? _a : null;
    if (null === step || 'STARTING' === step.status) {
        return translate('akeneo_job_process_tracker.tracking.not_started');
    }
    switch (step.status) {
        case 'IN_PROGRESS':
            if (!step.is_trackable || 'FAILED' === jobExecutionRow.status) {
                return translate('akeneo_job_process_tracker.tracking.untrackable');
            }
            if (step.total_items === 0 || step.processed_items === 0) {
                return translate('akeneo_job_process_tracker.tracking.estimating');
            }
            var percentProcessed = (step.processed_items * 100) / step.total_items;
            var durationProjection = Math.round((step.duration * 100) / percentProcessed);
            var durationLeft = durationProjection - step.duration;
            return translate('akeneo_job_process_tracker.tracking.in_progress', {
                duration: (0, intl_duration_1.formatSecondsIntl)(translate, durationLeft),
            });
        case 'ABANDONED':
        case 'COMPLETED':
        case 'FAILED':
        case 'STOPPED':
        case 'STOPPING':
        case 'UNKNOWN':
        default:
            var totalDuration = jobExecutionRow.tracking.steps.reduce(function (total, step) { return total + step.duration; }, 0);
            return translate('akeneo_job_process_tracker.tracking.completed', {
                duration: (0, intl_duration_1.formatSecondsIntl)(translate, totalDuration),
            });
    }
};
exports.getJobExecutionRowTrackingProgressLabel = getJobExecutionRowTrackingProgressLabel;
//# sourceMappingURL=JobExecutionTable.js.map
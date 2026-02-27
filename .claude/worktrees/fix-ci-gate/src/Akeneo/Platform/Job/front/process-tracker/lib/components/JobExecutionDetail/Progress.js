"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Progress = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var models_1 = require("../../models");
var shared_1 = require("@akeneo-pim-community/shared");
var intl_duration_1 = require("../../tools/intl-duration");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: grid;\n  grid-auto-flow: column;\n  grid-gap: 5px;\n  grid-auto-columns: 1fr;\n  margin-top: 40px;\n"], ["\n  display: grid;\n  grid-auto-flow: column;\n  grid-gap: 5px;\n  grid-auto-columns: 1fr;\n  margin-top: 40px;\n"])));
var guessStepExecutionTrackingLevel = function (step) {
    if (step.hasError)
        return 'danger';
    if (step.hasWarning)
        return 'warning';
    if ((0, models_1.isPaused)(step.status))
        return 'tertiary';
    return 'primary';
};
var getStepExecutionTrackingPercent = function (step) {
    if (step.status === 'STARTING')
        return 0;
    if (step.status === 'COMPLETED')
        return 100;
    if (step.totalItems === 0 || !step.isTrackable) {
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
    return (step.processedItems * 100) / step.totalItems;
};
var getStepExecutionTrackingTitle = function (translate, step) {
    var key = "batch_jobs." + step.jobName + "." + step.stepName + ".label";
    if (translate(key) === key) {
        key = "batch_jobs.default_steps." + step.stepName;
    }
    return translate(key);
};
var getStepExecutionTrackingProgressLabel = function (translate, jobStatus, step) {
    switch (step.status) {
        case 'STARTING':
            return translate('pim_import_export.tracking.not_started');
        case 'IN_PROGRESS':
            if (!step.isTrackable || 'Failed' === jobStatus) {
                return translate('pim_import_export.tracking.untrackable');
            }
            if (step.totalItems === 0 || step.processedItems === 0) {
                return translate('pim_import_export.tracking.estimating');
            }
            var percentProcessed = (step.processedItems * 100) / step.totalItems;
            var durationProjection = Math.round((step.duration * 100) / percentProcessed);
            var durationLeft = durationProjection - step.duration;
            return translate('pim_import_export.tracking.in_progress', {
                duration: (0, intl_duration_1.formatSecondsIntl)(translate, durationLeft),
            });
        case 'PAUSED':
        case 'PAUSING':
            return translate("akeneo_job.job_status." + step.status);
        case 'ABANDONED':
        case 'COMPLETED':
        case 'FAILED':
        case 'STOPPED':
        case 'STOPPING':
        case 'UNKNOWN':
        default:
            return translate('pim_import_export.tracking.completed', { duration: (0, intl_duration_1.formatSecondsIntl)(translate, step.duration) });
    }
};
var Progress = function (_a) {
    var jobStatus = _a.jobStatus, steps = _a.steps;
    var translate = (0, shared_1.useTranslate)();
    return (react_1.default.createElement(Container, null, steps.map(function (step, index) { return (react_1.default.createElement(akeneo_design_system_1.ProgressBar, { key: index, title: getStepExecutionTrackingTitle(translate, step), progressLabel: getStepExecutionTrackingProgressLabel(translate, jobStatus, step), level: guessStepExecutionTrackingLevel(step), percent: getStepExecutionTrackingPercent(step), size: "large" })); })));
};
exports.Progress = Progress;
var templateObject_1;
//# sourceMappingURL=Progress.js.map
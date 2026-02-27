"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.JobExecutionStatus = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../../models");
var StatusContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n"], ["\n  display: flex;\n  align-items: center;\n"])));
var badgeLevel = function (status, hasError, hasWarning) {
    switch (true) {
        case status === 'FAILED' || hasError:
            return 'danger';
        case hasWarning:
            return 'warning';
        case (0, models_1.isPaused)(status):
            return 'tertiary';
        default:
            return 'primary';
    }
};
var JobExecutionStatus = function (_a) {
    var status = _a.status, currentStep = _a.currentStep, totalSteps = _a.totalSteps, hasWarning = _a.hasWarning, hasError = _a.hasError, props = __rest(_a, ["status", "currentStep", "totalSteps", "hasWarning", "hasError"]);
    var translate = (0, shared_1.useTranslate)();
    var level = badgeLevel(status, hasError, hasWarning);
    var showTooltip = (0, models_1.isPaused)(status);
    var label = (0, models_1.isInProgress)(status)
        ? translate("akeneo_job.job_status." + status) + " " + currentStep + "/" + totalSteps
        : translate("akeneo_job.job_status." + status);
    return (react_1.default.createElement(StatusContainer, null,
        react_1.default.createElement(akeneo_design_system_1.Badge, __assign({ level: level }, props), label),
        showTooltip && (react_1.default.createElement(akeneo_design_system_1.Tooltip, { direction: "bottom", iconSize: 18 }, translate('akeneo_job_process_tracker.tooltip.paused')))));
};
exports.JobExecutionStatus = JobExecutionStatus;
var templateObject_1;
//# sourceMappingURL=JobExecutionStatus.js.map
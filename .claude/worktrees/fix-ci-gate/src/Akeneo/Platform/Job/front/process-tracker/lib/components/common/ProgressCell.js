"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.ProgressCell = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var models_1 = require("../../models");
var shared_1 = require("@akeneo-pim-community/shared");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: grid;\n  grid-auto-flow: column;\n  grid-gap: 4px;\n  grid-auto-columns: 1fr;\n  flex: 1;\n  min-width: 100px;\n  max-width: 200px;\n"], ["\n  display: grid;\n  grid-auto-flow: column;\n  grid-gap: 4px;\n  grid-auto-columns: 1fr;\n  flex: 1;\n  min-width: 100px;\n  max-width: 200px;\n"])));
var ProgressCell = function (_a) {
    var jobExecutionRow = _a.jobExecutionRow;
    var translate = (0, shared_1.useTranslate)();
    return (react_1.default.createElement(akeneo_design_system_1.Table.Cell, { title: (0, models_1.getJobExecutionRowTrackingProgressLabel)(translate, jobExecutionRow) },
        react_1.default.createElement(Container, null, __spreadArray([], Array(jobExecutionRow.tracking.total_step), true).map(function (_, stepIndex) {
            var _a;
            if ('STARTING' === jobExecutionRow.status) {
                return react_1.default.createElement(akeneo_design_system_1.ProgressBar, { key: stepIndex, percent: "indeterminate", level: "primary" });
            }
            var step = (_a = jobExecutionRow.tracking.steps[stepIndex]) !== null && _a !== void 0 ? _a : null;
            return null === step ? (react_1.default.createElement(akeneo_design_system_1.ProgressBar, { key: stepIndex, level: "primary", percent: 0 })) : (react_1.default.createElement(akeneo_design_system_1.ProgressBar, { key: stepIndex, level: (0, models_1.getStepExecutionRowTrackingLevel)(step), percent: (0, models_1.getStepExecutionRowTrackingPercent)(step) }));
        }))));
};
exports.ProgressCell = ProgressCell;
var templateObject_1;
//# sourceMappingURL=ProgressCell.js.map
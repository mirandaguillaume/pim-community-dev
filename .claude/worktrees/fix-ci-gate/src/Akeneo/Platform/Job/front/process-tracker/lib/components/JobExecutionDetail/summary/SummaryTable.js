"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.SummaryTable = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var InnerTable_1 = require("./InnerTable");
var WarningHelper_1 = require("./WarningHelper");
var models_1 = require("../../../models");
var SpacedTable = (0, styled_components_1.default)(akeneo_design_system_1.Table)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-bottom: 40px;\n"], ["\n  margin-bottom: 40px;\n"])));
var SummaryCell = (0, styled_components_1.default)(akeneo_design_system_1.Table.Cell)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  padding: 0 10px;\n  max-width: unset;\n"], ["\n  padding: 0 10px;\n  max-width: unset;\n"])));
var LargeCell = styled_components_1.default.td.attrs({ colSpan: 5 })(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  padding: 0 0 1px 0;\n"], ["\n  padding: 0 0 1px 0;\n"])));
var getStepKey = function (_a) {
    var job = _a.job, label = _a.label;
    return "batch_jobs." + job + "." + label + ".label";
};
var getStepLabel = function (translate, step) {
    var key = getStepKey(step);
    if (translate(key) === key) {
        key = "batch_jobs.default_steps." + step.label;
    }
    return translate(key);
};
var getStepStatusLevel = function (step) {
    if (0 < step.errors.length || 0 < step.failures.length) {
        return 'danger';
    }
    if (0 < step.warnings.length) {
        return 'warning';
    }
    if ((0, models_1.isPaused)(step.status_code)) {
        return 'tertiary';
    }
    return 'primary';
};
var SummaryTable = function (_a) {
    var jobExecution = _a.jobExecution;
    var translate = (0, shared_1.useTranslate)();
    if (!jobExecution.stepExecutions)
        return null;
    return (react_1.default.createElement(SpacedTable, null,
        react_1.default.createElement(akeneo_design_system_1.Table.Header, { sticky: 0 },
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null, translate('pim_import_export.form.job_execution.summary.header.step')),
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null, translate('pim_common.status')),
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null, translate('pim_import_export.form.job_execution.summary.header.summary')),
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null, translate('pim_import_export.form.job_execution.summary.header.start')),
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null, translate('pim_import_export.form.job_execution.summary.header.end'))),
        react_1.default.createElement(akeneo_design_system_1.Table.Body, null,
            jobExecution.stepExecutions.map(function (step) { return (react_1.default.createElement(react_1.default.Fragment, { key: getStepKey(step) },
                react_1.default.createElement(akeneo_design_system_1.Table.Row, null,
                    react_1.default.createElement(akeneo_design_system_1.Table.Cell, { rowTitle: true, title: getStepLabel(translate, step) }, getStepLabel(translate, step)),
                    react_1.default.createElement(akeneo_design_system_1.Table.Cell, null,
                        react_1.default.createElement(akeneo_design_system_1.Badge, { level: getStepStatusLevel(step) }, step.status)),
                    react_1.default.createElement(SummaryCell, null,
                        react_1.default.createElement(InnerTable_1.InnerTable, { content: step.summary })),
                    react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, step.startedAt),
                    react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, step.endedAt)),
                step.errors.map(function (error, index) { return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { key: index },
                    react_1.default.createElement(LargeCell, null,
                        react_1.default.createElement(akeneo_design_system_1.Helper, { level: "error" }, error)))); }),
                step.warnings.map(function (warning, index) { return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { key: index },
                    react_1.default.createElement(LargeCell, null,
                        react_1.default.createElement(WarningHelper_1.WarningHelper, { key: index, warning: warning })))); }),
                step.failures.map(function (failure, index) { return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { key: index },
                    react_1.default.createElement(LargeCell, null,
                        react_1.default.createElement(akeneo_design_system_1.Helper, { level: "error" }, 'string' === typeof failure ? failure : failure.label)))); }))); }),
            jobExecution.failures.map(function (failure, index) { return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { key: index },
                react_1.default.createElement(LargeCell, null,
                    react_1.default.createElement(akeneo_design_system_1.Helper, { level: "error" }, 'string' === typeof failure ? failure : failure.label)))); }))));
};
exports.SummaryTable = SummaryTable;
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=SummaryTable.js.map
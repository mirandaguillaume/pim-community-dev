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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.LastOperationsWidget = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../models");
var common_1 = require("../components/common");
var hooks_1 = require("../hooks");
var lastOperationsWidgetFilter = __assign(__assign({}, (0, models_1.getDefaultJobExecutionFilter)()), { size: 10 });
var COLUMN_HEADERS = ['started_at', 'type', 'job_name', 'username', 'status', 'warning_count'];
var LastOperationsWidget = function () {
    var _a, _b;
    var translate = (0, shared_1.useTranslate)();
    var dateFormatter = (0, shared_1.useDateFormatter)();
    var router = (0, shared_1.useRouter)();
    var jobExecutionTable = (0, hooks_1.useJobExecutionTable)(lastOperationsWidgetFilter, false)[0];
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
            react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('akeneo_job_process_tracker.last_operations.title')),
            react_1.default.createElement(akeneo_design_system_1.SectionTitle.Spacer, null),
            react_1.default.createElement(akeneo_design_system_1.Button, { href: "#" + router.generate('akeneo_job_process_tracker_index') }, translate('akeneo_job_process_tracker.last_operations.view_all'))),
        0 === ((_a = jobExecutionTable === null || jobExecutionTable === void 0 ? void 0 : jobExecutionTable.rows) === null || _a === void 0 ? void 0 : _a.length) ? (react_1.default.createElement(akeneo_design_system_1.Placeholder, { title: translate('akeneo_job_process_tracker.last_operations.no_result'), illustration: react_1.default.createElement(akeneo_design_system_1.SettingsIllustration, null) })) : (react_1.default.createElement(akeneo_design_system_1.Table, null,
            react_1.default.createElement(akeneo_design_system_1.Table.Header, null,
                COLUMN_HEADERS.map(function (columnHeader) { return (react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, { key: columnHeader }, translate("akeneo_job_process_tracker.job_execution_list.table.headers." + columnHeader))); }),
                react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null)),
            react_1.default.createElement(akeneo_design_system_1.Table.Body, null, (_b = jobExecutionTable === null || jobExecutionTable === void 0 ? void 0 : jobExecutionTable.rows) === null || _b === void 0 ? void 0 : _b.map(function (jobExecutionRow) { return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { key: jobExecutionRow.job_execution_id },
                react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, jobExecutionRow.started_at
                    ? dateFormatter(jobExecutionRow.started_at, {
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                    })
                    : '-'),
                react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, translate("akeneo_job_process_tracker.type_filter." + jobExecutionRow.type)),
                react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, jobExecutionRow.job_name),
                react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, jobExecutionRow.username),
                react_1.default.createElement(akeneo_design_system_1.Table.Cell, null,
                    react_1.default.createElement(common_1.JobExecutionStatus, { status: jobExecutionRow.status, hasWarning: 0 < jobExecutionRow.warning_count, hasError: jobExecutionRow.has_error, currentStep: jobExecutionRow.tracking.current_step, totalSteps: jobExecutionRow.tracking.total_step })),
                react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, 0 < jobExecutionRow.warning_count ? jobExecutionRow.warning_count : '-'),
                react_1.default.createElement(akeneo_design_system_1.Table.Cell, null,
                    react_1.default.createElement(akeneo_design_system_1.Button, { href: "#" + router.generate('akeneo_job_process_tracker_details', {
                            id: jobExecutionRow.job_execution_id,
                        }), ghost: true, level: "tertiary", size: "small" }, translate('akeneo_job_process_tracker.last_operations.details'))))); }))))));
};
exports.LastOperationsWidget = LastOperationsWidget;
//# sourceMappingURL=LastOperationsWidget.js.map
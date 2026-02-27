"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.LastExecutionTable = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../models");
var common_1 = require("./common");
var COLUMN_HEADERS = ['started_at', 'username', 'progress', 'status', 'warning_count'];
var LastExecutionTable = function (_a) {
    var sticky = _a.sticky, jobExecutionRows = _a.jobExecutionRows, onTableRefresh = _a.onTableRefresh;
    var translate = (0, shared_1.useTranslate)();
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var dateFormatter = (0, shared_1.useDateFormatter)();
    var router = (0, shared_1.useRouter)();
    var handleRowClick = function (jobExecutionId) { return function (event) {
        var route = router.generate('akeneo_job_process_tracker_details', { id: jobExecutionId });
        if (event.metaKey || event.ctrlKey) {
            var newTab = window.open("#" + route, '_blank');
            newTab === null || newTab === void 0 ? void 0 : newTab.focus();
            return;
        }
        router.redirect(route);
    }; };
    return (react_1.default.createElement(akeneo_design_system_1.Table, null,
        react_1.default.createElement(akeneo_design_system_1.Table.Header, { sticky: sticky },
            COLUMN_HEADERS.map(function (sortableColumnHeader) { return (react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, { key: sortableColumnHeader }, translate("akeneo_job_process_tracker.job_execution_list.table.headers." + sortableColumnHeader))); }),
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null)),
        react_1.default.createElement(akeneo_design_system_1.Table.Body, null, jobExecutionRows.map(function (jobExecutionRow) { return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { key: jobExecutionRow.job_execution_id, onClick: (0, models_1.canShowJobExecutionDetail)(isGranted, jobExecutionRow)
                ? function (event) { return handleRowClick(jobExecutionRow.job_execution_id)(event); }
                : undefined },
            react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, jobExecutionRow.started_at
                ? dateFormatter(jobExecutionRow.started_at, {
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                })
                : '-'),
            react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, jobExecutionRow.username),
            react_1.default.createElement(common_1.ProgressCell, { jobExecutionRow: jobExecutionRow }),
            react_1.default.createElement(akeneo_design_system_1.Table.Cell, null,
                react_1.default.createElement(common_1.JobExecutionStatus, { status: jobExecutionRow.status, hasWarning: jobExecutionRow.warning_count > 0, hasError: jobExecutionRow.has_error, currentStep: jobExecutionRow.tracking.current_step, totalSteps: jobExecutionRow.tracking.total_step })),
            react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, jobExecutionRow.warning_count > 0 ? jobExecutionRow.warning_count : '-'),
            react_1.default.createElement(akeneo_design_system_1.Table.ActionCell, null,
                react_1.default.createElement(common_1.StopJobAction, { id: jobExecutionRow.job_execution_id.toString(), jobLabel: jobExecutionRow.job_name, isStoppable: (0, models_1.jobCanBeStopped)(jobExecutionRow), onStop: onTableRefresh, ghost: true, size: "small" })))); }))));
};
exports.LastExecutionTable = LastExecutionTable;
//# sourceMappingURL=LastExecutionTable.js.map
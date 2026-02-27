"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.JobExecutionTable = void 0;
var react_1 = __importStar(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../../models");
var common_1 = require("../common");
var COLUMN_HEADERS = [
    { name: 'job_name', isSortable: true },
    { name: 'type', isSortable: true },
    { name: 'started_at', isSortable: true },
    { name: 'username', isSortable: true },
    { name: 'progress', isSortable: false },
    { name: 'status', isSortable: true },
    { name: 'warning_count', isSortable: false },
];
var JobExecutionTable = function (_a) {
    var sticky = _a.sticky, jobExecutionRows = _a.jobExecutionRows, onSortChange = _a.onSortChange, currentSort = _a.currentSort, onTableRefresh = _a.onTableRefresh;
    var translate = (0, shared_1.useTranslate)();
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var dateFormatter = (0, shared_1.useDateFormatter)();
    var sortDirection = 'ASC' === currentSort.direction ? 'ascending' : 'descending';
    var router = (0, shared_1.useRouter)();
    var handleRowClick = (0, react_1.useCallback)(function (jobExecutionId) { return function (event) {
        var route = router.generate('akeneo_job_process_tracker_details', { id: jobExecutionId });
        if (event.metaKey || event.ctrlKey) {
            var newTab = window.open("#" + route, '_blank');
            newTab === null || newTab === void 0 ? void 0 : newTab.focus();
            return;
        }
        router.redirect(route);
    }; }, [router]);
    return (react_1.default.createElement(akeneo_design_system_1.Table, null,
        react_1.default.createElement(akeneo_design_system_1.Table.Header, { sticky: sticky },
            COLUMN_HEADERS.map(function (_a) {
                var name = _a.name, isSortable = _a.isSortable;
                return (react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, { key: name, isSortable: isSortable, onDirectionChange: isSortable
                        ? function (direction) {
                            if ('none' !== direction) {
                                onSortChange({ column: name, direction: 'ascending' === direction ? 'ASC' : 'DESC' });
                            }
                        }
                        : undefined, sortDirection: isSortable ? (currentSort.column === name ? sortDirection : 'none') : undefined }, translate("akeneo_job_process_tracker.job_execution_list.table.headers." + name)));
            }),
            react_1.default.createElement(akeneo_design_system_1.Table.HeaderCell, null)),
        react_1.default.createElement(akeneo_design_system_1.Table.Body, null, jobExecutionRows.map(function (jobExecutionRow) { return (react_1.default.createElement(akeneo_design_system_1.Table.Row, { key: jobExecutionRow.job_execution_id, onClick: (0, models_1.canShowJobExecutionDetail)(isGranted, jobExecutionRow)
                ? function (event) { return handleRowClick(jobExecutionRow.job_execution_id)(event); }
                : undefined },
            react_1.default.createElement(akeneo_design_system_1.Table.Cell, { rowTitle: true }, jobExecutionRow.job_name),
            react_1.default.createElement(akeneo_design_system_1.Table.Cell, null, translate("akeneo_job_process_tracker.type_filter." + jobExecutionRow.type)),
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
exports.JobExecutionTable = JobExecutionTable;
//# sourceMappingURL=JobExecutionTable.js.map
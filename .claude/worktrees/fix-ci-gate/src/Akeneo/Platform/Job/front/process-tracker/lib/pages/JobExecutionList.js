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
exports.JobExecutionList = void 0;
var react_1 = __importStar(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var hooks_1 = require("../hooks");
var components_1 = require("../components");
var models_1 = require("../models");
var useStoredJobExecutionFilter_1 = require("../hooks/useStoredJobExecutionFilter");
var MAX_PAGE_WITHOUT_FILTER = 50;
var JobExecutionList = function () {
    var activityHref = (0, shared_1.useRoute)('pim_dashboard_index');
    var translate = (0, shared_1.useTranslate)();
    var _a = (0, useStoredJobExecutionFilter_1.useStoredJobExecutionFilter)(), jobExecutionFilter = _a[0], setJobExecutionFilter = _a[1];
    var _b = (0, hooks_1.useJobExecutionTable)(jobExecutionFilter), jobExecutionTable = _b[0], refreshJobExecutionTable = _b[1];
    var matchesCount = jobExecutionTable === null ? 0 : jobExecutionTable.matches_count;
    var displayPaginationWarning = MAX_PAGE_WITHOUT_FILTER === jobExecutionFilter.page;
    var handlePageChange = function (page) {
        setJobExecutionFilter(function (jobExecutionFilter) { return (__assign(__assign({}, jobExecutionFilter), { page: page })); });
    };
    var handleSortChange = function (sort) {
        setJobExecutionFilter(function (jobExecutionFilter) { return (__assign(__assign({}, jobExecutionFilter), { page: 1, sort: sort })); });
    };
    var handleStatusFilterChange = function (status) {
        setJobExecutionFilter(function (jobExecutionFilter) { return (__assign(__assign({}, jobExecutionFilter), { page: 1, status: status })); });
    };
    var handleTypeFilterChange = function (type) {
        setJobExecutionFilter(function (jobExecutionFilter) { return (__assign(__assign({}, jobExecutionFilter), { page: 1, type: type })); });
    };
    var handleUserFilterChange = function (user) {
        setJobExecutionFilter(function (jobExecutionFilter) { return (__assign(__assign({}, jobExecutionFilter), { page: 1, user: user })); });
    };
    var handleAutomationFilterChange = function (automation) {
        setJobExecutionFilter(function (jobExecutionFilter) { return (__assign(__assign({}, jobExecutionFilter), { page: 1, automation: automation })); });
    };
    var handleSearchChange = (0, react_1.useCallback)(function (search) {
        setJobExecutionFilter(function (jobExecutionFilter) { return (__assign(__assign({}, jobExecutionFilter), { page: 1, search: search })); });
    }, [setJobExecutionFilter]);
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(shared_1.PageHeader, { showPlaceholder: null === jobExecutionTable },
            react_1.default.createElement(shared_1.PageHeader.Breadcrumb, null,
                react_1.default.createElement(akeneo_design_system_1.Breadcrumb, null,
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, { href: "#" + activityHref }, translate('pim_menu.tab.activity')),
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, null, translate('pim_menu.item.job_tracker')))),
            react_1.default.createElement(shared_1.PageHeader.UserActions, null,
                react_1.default.createElement(shared_1.PimView, { className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu", viewName: "pim-process-tracker-user-navigation" })),
            react_1.default.createElement(shared_1.PageHeader.Title, null, translate('pim_enrich.entity.job_execution.page_title.index', { count: matchesCount.toString() }, matchesCount))),
        react_1.default.createElement(shared_1.PageContent, null, jobExecutionTable && (react_1.default.createElement(react_1.default.Fragment, null,
            react_1.default.createElement(components_1.JobExecutionSearchBar, { jobExecutionFilter: jobExecutionFilter, onStatusFilterChange: handleStatusFilterChange, onTypeFilterChange: handleTypeFilterChange, onUserFilterChange: handleUserFilterChange, onAutomationFilterChange: handleAutomationFilterChange, onSearchChange: handleSearchChange }),
            0 < matchesCount && (react_1.default.createElement(react_1.default.Fragment, null,
                displayPaginationWarning && (react_1.default.createElement(akeneo_design_system_1.Helper, { level: "warning", sticky: 44 }, translate('akeneo_job_process_tracker.max_page_without_filter_helper'))),
                react_1.default.createElement(akeneo_design_system_1.Pagination, { sticky: displayPaginationWarning ? 88 : 44, itemsPerPage: jobExecutionFilter.size, currentPage: jobExecutionFilter.page, totalItems: Math.min(matchesCount, MAX_PAGE_WITHOUT_FILTER * jobExecutionFilter.size), followPage: handlePageChange }),
                react_1.default.createElement(components_1.JobExecutionTable, { sticky: jobExecutionFilter.size < matchesCount ? (displayPaginationWarning ? 132 : 88) : 44, jobExecutionRows: jobExecutionTable.rows, onSortChange: handleSortChange, onTableRefresh: refreshJobExecutionTable, currentSort: jobExecutionFilter.sort }))),
            0 === matchesCount && (0, models_1.isDefaultJobExecutionFilter)(jobExecutionFilter) && (react_1.default.createElement(shared_1.NoDataSection, null,
                react_1.default.createElement(akeneo_design_system_1.AttributesIllustration, { size: 256 }),
                react_1.default.createElement(shared_1.NoDataTitle, null, translate('pim_common.no_result')))),
            0 === matchesCount && !(0, models_1.isDefaultJobExecutionFilter)(jobExecutionFilter) && (react_1.default.createElement(shared_1.NoDataSection, null,
                react_1.default.createElement(akeneo_design_system_1.AttributesIllustration, { size: 256 }),
                react_1.default.createElement(shared_1.NoDataTitle, null, translate('pim_common.no_search_result')))))))));
};
exports.JobExecutionList = JobExecutionList;
//# sourceMappingURL=JobExecutionList.js.map
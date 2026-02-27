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
exports.JobExecutionSearchBar = void 0;
var react_1 = __importStar(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var TypeFilter_1 = require("./TypeFilter");
var StatusFilter_1 = require("./StatusFilter");
var UserFilter_1 = require("./UserFilter");
var AutomationFilter_1 = require("./AutomationFilter");
var JobExecutionSearchBar = function (_a) {
    var jobExecutionFilter = _a.jobExecutionFilter, onStatusFilterChange = _a.onStatusFilterChange, onTypeFilterChange = _a.onTypeFilterChange, onUserFilterChange = _a.onUserFilterChange, onAutomationFilterChange = _a.onAutomationFilterChange, onSearchChange = _a.onSearchChange;
    var translate = (0, shared_1.useTranslate)();
    var _b = (0, react_1.useState)(jobExecutionFilter.search), userSearch = _b[0], setUserSearch = _b[1];
    var debouncedUserSearch = (0, akeneo_design_system_1.useDebounce)(userSearch, 250);
    var inputRef = (0, react_1.useRef)(null);
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var canViewAllJobs = isGranted('pim_enrich_job_tracker_view_all_jobs');
    (0, akeneo_design_system_1.useAutoFocus)(inputRef);
    (0, react_1.useEffect)(function () {
        onSearchChange(debouncedUserSearch);
    }, [debouncedUserSearch, onSearchChange]);
    return (react_1.default.createElement(akeneo_design_system_1.Search, { inputRef: inputRef, sticky: 0, placeholder: translate('akeneo_job_process_tracker.job_execution_list.search_placeholder'), searchValue: userSearch, onSearchChange: setUserSearch },
        canViewAllJobs && (react_1.default.createElement(AutomationFilter_1.AutomationFilter, { automationFilterValue: jobExecutionFilter.automation, onAutomationFilterChange: onAutomationFilterChange })),
        react_1.default.createElement(TypeFilter_1.TypeFilter, { typeFilterValue: jobExecutionFilter.type, onTypeFilterChange: onTypeFilterChange }),
        react_1.default.createElement(StatusFilter_1.StatusFilter, { statusFilterValue: jobExecutionFilter.status, onStatusFilterChange: onStatusFilterChange }),
        react_1.default.createElement(UserFilter_1.UserFilter, { userFilterValue: jobExecutionFilter.user, onUserFilterChange: onUserFilterChange })));
};
exports.JobExecutionSearchBar = JobExecutionSearchBar;
//# sourceMappingURL=JobExecutionSearchBar.js.map
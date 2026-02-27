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
exports.JobInstanceDetail = void 0;
var react_1 = __importStar(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var hooks_1 = require("../hooks");
var components_1 = require("../components");
var JobInstanceDetail = function (_a) {
    var code = _a.code, type = _a.type;
    var translate = (0, shared_1.useTranslate)();
    var filter = (0, react_1.useMemo)(function () { return ({
        page: 1,
        sort: { column: 'started_at', direction: 'DESC' },
        automation: null,
        status: [],
        size: 25,
        search: '',
        user: [],
        type: [type],
        code: [code],
    }); }, [code, type]);
    var _b = (0, hooks_1.useJobExecutionTable)(filter), jobExecutionTable = _b[0], refreshJobExecutionTable = _b[1];
    if (null === jobExecutionTable) {
        return null;
    }
    return (react_1.default.createElement(shared_1.Section, null,
        react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
            react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('akeneo_job_process_tracker.last_executions.title'))),
        0 < jobExecutionTable.matches_count && (react_1.default.createElement(components_1.LastExecutionTable, { jobExecutionRows: jobExecutionTable.rows, onTableRefresh: refreshJobExecutionTable })),
        0 === jobExecutionTable.matches_count && (react_1.default.createElement(shared_1.NoDataSection, null,
            react_1.default.createElement(akeneo_design_system_1.AttributesIllustration, { size: 256 }),
            react_1.default.createElement(shared_1.NoDataTitle, null, translate('pim_common.no_result'))))));
};
exports.JobInstanceDetail = JobInstanceDetail;
//# sourceMappingURL=JobInstanceDetail.js.map
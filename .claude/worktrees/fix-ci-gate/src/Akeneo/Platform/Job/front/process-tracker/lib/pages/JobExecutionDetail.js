"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.JobExecutionDetail = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var components_1 = require("../components");
var models_1 = require("../models");
var useJobExecution_1 = require("../hooks/useJobExecution");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  height: 100vh;\n"], ["\n  display: flex;\n  flex-direction: column;\n  height: 100vh;\n"])));
var Refreshing = styled_components_1.default.span(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n  align-items: center;\n  font-style: italic;\n  color: ", ";\n  text-align: right;\n"], ["\n  display: flex;\n  gap: 10px;\n  align-items: center;\n  font-style: italic;\n  color: ", ";\n  text-align: right;\n"])), (0, akeneo_design_system_1.getColor)('grey', 100));
var StatusContainer = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  margin-top: 8px;\n  display: flex;\n  align-items: center;\n  gap: 5px;\n"], ["\n  margin-top: 8px;\n  display: flex;\n  align-items: center;\n  gap: 5px;\n"])));
var canDownloadLog = function (_a, jobExecution) {
    var isGranted = _a.isGranted;
    if (!jobExecution || !jobExecution.meta.logExists) {
        return false;
    }
    if (jobExecution.jobInstance.type === 'export') {
        return isGranted('pim_importexport_export_execution_download_log');
    }
    else if (jobExecution.jobInstance.type === 'import') {
        return isGranted('pim_importexport_import_execution_download_log');
    }
    return true;
};
var canDownloadArchive = function (_a, jobExecution) {
    var isGranted = _a.isGranted;
    if (!jobExecution) {
        return false;
    }
    if (jobExecution.jobInstance.type === 'export') {
        return isGranted('pim_importexport_export_execution_download_files');
    }
    else if (jobExecution.jobInstance.type === 'import') {
        return isGranted('pim_importexport_import_execution_download_files');
    }
    return true;
};
var JobExecutionDetail = function (_a) {
    var _b, _c, _d, _e;
    var jobExecutionId = _a.jobExecutionId;
    var translate = (0, shared_1.useTranslate)();
    var security = (0, shared_1.useSecurity)();
    var router = (0, shared_1.useRouter)();
    var jobTypeWithProfile = ['import', 'export'];
    var _f = (0, useJobExecution_1.useJobExecution)(jobExecutionId), jobExecution = _f[0], error = _f[1], reloadJobExecution = _f[2], isAutoRefreshing = _f[3];
    var handleStop = function () { return __awaiter(void 0, void 0, void 0, function () {
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0: return [4, reloadJobExecution()];
                case 1:
                    _a.sent();
                    return [2];
            }
        });
    }); };
    var _g = (0, akeneo_design_system_1.useBooleanState)(false), secondaryActionIsOpen = _g[0], openSecondaryAction = _g[1], closeSecondaryAction = _g[2];
    var _h = (0, akeneo_design_system_1.useBooleanState)(false), downloadDropdownIsOpen = _h[0], openDownloadDropdown = _h[1], closeDownloadDropdown = _h[2];
    var downloadLogIsVisible = canDownloadLog(security, jobExecution);
    var downloadArchiveLinks = (0, models_1.getDownloadLinks)((_b = jobExecution === null || jobExecution === void 0 ? void 0 : jobExecution.meta.archives) !== null && _b !== void 0 ? _b : null);
    var downloadArchiveLinkIsVisible = canDownloadArchive(security, jobExecution) && 0 < downloadArchiveLinks.length;
    var downloadZipArchive = (_d = (_c = jobExecution === null || jobExecution === void 0 ? void 0 : jobExecution.meta) === null || _c === void 0 ? void 0 : _c.generateZipArchive) !== null && _d !== void 0 ? _d : false;
    var showProfileIsVisible = jobTypeWithProfile.includes((jobExecution === null || jobExecution === void 0 ? void 0 : jobExecution.jobInstance.type) || '');
    var dashboardHref = (0, shared_1.useRoute)('pim_dashboard_index');
    var jobTrackerHref = (0, shared_1.useRoute)('akeneo_job_process_tracker_index');
    var downloadLogHref = (0, shared_1.useRoute)('pim_importexport_export_execution_download_log', {
        id: jobExecutionId,
    });
    if (error) {
        return (react_1.default.createElement(shared_1.FullScreenError, { title: translate('error.exception', { status_code: error.statusCode.toString() }), message: error.statusMessage, code: error.statusCode }));
    }
    return (react_1.default.createElement(Container, null,
        react_1.default.createElement(shared_1.PageHeader, { showPlaceholder: null === jobExecution },
            react_1.default.createElement(shared_1.PageHeader.Breadcrumb, null,
                react_1.default.createElement(akeneo_design_system_1.Breadcrumb, null,
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, { href: "#" + dashboardHref }, translate('pim_menu.tab.activity')),
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, { href: "#" + jobTrackerHref }, translate('pim_menu.item.job_tracker')),
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, null, translate('pim_import_export.form.job_execution.title.details')))),
            react_1.default.createElement(shared_1.PageHeader.UserActions, null,
                null !== jobExecution && isAutoRefreshing && (react_1.default.createElement(Refreshing, null,
                    translate('pim_import_export.form.job_execution.refreshing'),
                    react_1.default.createElement(akeneo_design_system_1.LoaderIcon, null))),
                react_1.default.createElement(shared_1.PimView, { viewName: "pim-menu-user-navigation", className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu" })),
            react_1.default.createElement(shared_1.PageHeader.Actions, null,
                (showProfileIsVisible || downloadLogIsVisible) && (react_1.default.createElement(akeneo_design_system_1.Dropdown, null,
                    react_1.default.createElement(akeneo_design_system_1.IconButton, { level: "tertiary", title: translate('pim_common.other_actions'), icon: react_1.default.createElement(akeneo_design_system_1.MoreIcon, null), ghost: "borderless", onClick: openSecondaryAction }),
                    secondaryActionIsOpen && (react_1.default.createElement(akeneo_design_system_1.Dropdown.Overlay, { onClose: closeSecondaryAction },
                        react_1.default.createElement(akeneo_design_system_1.Dropdown.Header, null,
                            react_1.default.createElement(akeneo_design_system_1.Dropdown.Title, null, translate('pim_common.other_actions'))),
                        react_1.default.createElement(akeneo_design_system_1.Dropdown.ItemCollection, null,
                            showProfileIsVisible && jobExecution && (react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, null,
                                react_1.default.createElement(components_1.ShowProfile, { jobInstance: jobExecution.jobInstance }))),
                            downloadLogIsVisible && (react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, null,
                                react_1.default.createElement(akeneo_design_system_1.Link, { href: downloadLogHref }, translate('pim_import_export.form.job_execution.button.download_log.title'))))))))),
                jobExecution && (react_1.default.createElement(components_1.StopJobAction, { id: jobExecutionId, jobLabel: jobExecution.jobInstance.label, isStoppable: jobExecution.isStoppable, onStop: handleStop })),
                jobExecution &&
                    downloadArchiveLinkIsVisible &&
                    (downloadArchiveLinks.length === 1 && !downloadZipArchive ? (react_1.default.createElement(akeneo_design_system_1.Button, { level: "secondary", href: router.generate('pim_enrich_job_tracker_download_file', {
                            id: jobExecutionId,
                            archiver: downloadArchiveLinks[0].archiver,
                            key: downloadArchiveLinks[0].key,
                        }) }, translate(downloadArchiveLinks[0].label))) : (react_1.default.createElement(akeneo_design_system_1.Dropdown, null,
                        react_1.default.createElement(akeneo_design_system_1.Button, { level: "secondary", "data-toggle": "dropdown", onClick: openDownloadDropdown }, translate('pim_enrich.entity.job_execution.module.download.dropdown_title', {}, downloadArchiveLinks.length)),
                        downloadDropdownIsOpen && (react_1.default.createElement(akeneo_design_system_1.Dropdown.Overlay, { onClose: closeDownloadDropdown },
                            react_1.default.createElement(akeneo_design_system_1.Dropdown.Header, null,
                                react_1.default.createElement(akeneo_design_system_1.Dropdown.Title, null, translate('pim_enrich.entity.job_execution.module.download.dropdown_title', {}, downloadArchiveLinks.length))),
                            react_1.default.createElement(akeneo_design_system_1.Dropdown.ItemCollection, null,
                                downloadArchiveLinks.map(function (link, index) { return (react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, { key: index },
                                    react_1.default.createElement(akeneo_design_system_1.Link, { href: router.generate('pim_enrich_job_tracker_download_file', {
                                            id: jobExecutionId,
                                            archiver: link.archiver,
                                            key: link.key,
                                        }) }, translate(link.label)))); }),
                                downloadZipArchive && (react_1.default.createElement(akeneo_design_system_1.Dropdown.Item, null,
                                    react_1.default.createElement(akeneo_design_system_1.Link, { href: router.generate('pim_enrich_job_tracker_download_zip_archive', {
                                            jobExecutionId: jobExecutionId,
                                        }) }, translate('pim_import_export.form.job_execution.button.download_archive.title'))))))))))),
            react_1.default.createElement(shared_1.PageHeader.Title, null, (_e = jobExecution === null || jobExecution === void 0 ? void 0 : jobExecution.jobInstance.label) !== null && _e !== void 0 ? _e : jobExecutionId),
            react_1.default.createElement(shared_1.PageHeader.Content, null,
                jobExecution && (react_1.default.createElement(StatusContainer, null,
                    translate('pim_common.status'),
                    react_1.default.createElement(components_1.JobExecutionStatus, { "data-testid": "job-status", status: jobExecution.tracking.status, currentStep: jobExecution.tracking.currentStep, totalSteps: jobExecution.tracking.totalSteps, hasWarning: jobExecution.tracking.warning, hasError: jobExecution.tracking.error }))),
                (jobExecution === null || jobExecution === void 0 ? void 0 : jobExecution.tracking) && react_1.default.createElement(components_1.Progress, { jobStatus: jobExecution === null || jobExecution === void 0 ? void 0 : jobExecution.status, steps: jobExecution.tracking.steps }))),
        react_1.default.createElement(shared_1.PageContent, null, jobExecution && react_1.default.createElement(components_1.SummaryTable, { jobExecution: jobExecution }))));
};
exports.JobExecutionDetail = JobExecutionDetail;
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=JobExecutionDetail.js.map
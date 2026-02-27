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
exports.GoogleCloudStorageConfigurator = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../../models");
var CheckStorageConnection_1 = require("./CheckStorageConnection");
var GoogleCloudStorageConfigurator = function (_a) {
    var jobInstanceCode = _a.jobInstanceCode, storage = _a.storage, fileExtension = _a.fileExtension, validationErrors = _a.validationErrors, onStorageChange = _a.onStorageChange;
    if (!(0, models_1.isGoogleCloudStorage)(storage)) {
        throw new Error("Invalid storage type \"" + storage.type + "\" for google cloud storage configurator");
    }
    var translate = (0, shared_1.useTranslate)();
    var serviceAccountIsStoredOnServer = storage.service_account === undefined;
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.file_path, label: translate('pim_import_export.form.job_instance.storage_form.file_path.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.file_path.placeholder', {
                file_extension: fileExtension,
            }), onChange: function (file_path) { return onStorageChange(__assign(__assign({}, storage), { file_path: file_path })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[file_path]') }),
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.project_id, label: translate('pim_import_export.form.job_instance.storage_form.project_id.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.project_id.placeholder'), onChange: function (project_id) { return onStorageChange(__assign(__assign({}, storage), { project_id: project_id })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[project_id]') }),
        react_1.default.createElement(shared_1.TextField, { actions: serviceAccountIsStoredOnServer && (react_1.default.createElement(akeneo_design_system_1.Button, { level: "secondary", ghost: true, size: "small", onClick: function () { return onStorageChange(__assign(__assign({}, storage), { service_account: '' })); } }, translate('pim_common.edit'))), required: true, value: serviceAccountIsStoredOnServer ? '••••••••' : storage.service_account, readOnly: serviceAccountIsStoredOnServer, type: "password", label: translate('pim_import_export.form.job_instance.storage_form.service_account.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.service_account.placeholder'), onChange: function (service_account) { return onStorageChange(__assign(__assign({}, storage), { service_account: service_account })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[service_account]') }),
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.bucket, label: translate('pim_import_export.form.job_instance.storage_form.bucket.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.bucket.placeholder'), onChange: function (bucket) { return onStorageChange(__assign(__assign({}, storage), { bucket: bucket })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[bucket]') }),
        react_1.default.createElement(CheckStorageConnection_1.CheckStorageConnection, { jobInstanceCode: jobInstanceCode, storage: storage })));
};
exports.GoogleCloudStorageConfigurator = GoogleCloudStorageConfigurator;
//# sourceMappingURL=GoogleCloudStorageConfigurator.js.map
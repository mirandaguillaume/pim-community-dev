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
exports.MicrosoftAzureStorageConfigurator = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../../models");
var CheckStorageConnection_1 = require("./CheckStorageConnection");
var MicrosoftAzureStorageConfigurator = function (_a) {
    var jobInstanceCode = _a.jobInstanceCode, storage = _a.storage, fileExtension = _a.fileExtension, validationErrors = _a.validationErrors, onStorageChange = _a.onStorageChange;
    if (!(0, models_1.isMicrosoftAzureStorage)(storage)) {
        throw new Error("Invalid storage type \"" + storage.type + "\" for microsoft azure storage configurator");
    }
    var connectionStringIsStoredOnServer = storage.connection_string === undefined;
    var translate = (0, shared_1.useTranslate)();
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.file_path, label: translate('pim_import_export.form.job_instance.storage_form.file_path.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.file_path.placeholder', {
                file_extension: fileExtension,
            }), onChange: function (file_path) { return onStorageChange(__assign(__assign({}, storage), { file_path: file_path })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[file_path]') }),
        react_1.default.createElement(shared_1.TextField, { actions: connectionStringIsStoredOnServer && (react_1.default.createElement(akeneo_design_system_1.Button, { level: "secondary", ghost: true, size: "small", onClick: function () { return onStorageChange(__assign(__assign({}, storage), { connection_string: '' })); } }, translate('pim_common.edit'))), required: true, value: connectionStringIsStoredOnServer ? '••••••••' : storage.connection_string, readOnly: connectionStringIsStoredOnServer, type: "password", label: translate('pim_import_export.form.job_instance.storage_form.connection_string.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.connection_string.placeholder'), onChange: function (connection_string) { return onStorageChange(__assign(__assign({}, storage), { connection_string: connection_string })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[connection_string]') }),
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.container_name, label: translate('pim_import_export.form.job_instance.storage_form.container_name.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.container_name.placeholder'), onChange: function (container_name) { return onStorageChange(__assign(__assign({}, storage), { container_name: container_name })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[container_name]') }),
        react_1.default.createElement(CheckStorageConnection_1.CheckStorageConnection, { jobInstanceCode: jobInstanceCode, storage: storage })));
};
exports.MicrosoftAzureStorageConfigurator = MicrosoftAzureStorageConfigurator;
//# sourceMappingURL=MicrosoftAzureStorageConfigurator.js.map
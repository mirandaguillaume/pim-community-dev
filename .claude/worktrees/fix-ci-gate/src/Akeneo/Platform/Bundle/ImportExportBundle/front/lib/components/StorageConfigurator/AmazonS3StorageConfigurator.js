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
exports.AmazonS3StorageConfigurator = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var models_1 = require("../../models");
var CheckStorageConnection_1 = require("./CheckStorageConnection");
var AmazonS3StorageConfigurator = function (_a) {
    var jobInstanceCode = _a.jobInstanceCode, storage = _a.storage, fileExtension = _a.fileExtension, validationErrors = _a.validationErrors, onStorageChange = _a.onStorageChange;
    if (!(0, models_1.isAmazonS3Storage)(storage)) {
        throw new Error("Invalid storage type \"" + storage.type + "\" for amazon s3 storage configurator");
    }
    var translate = (0, shared_1.useTranslate)();
    var secretIsStoredOnServer = storage.secret === undefined;
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.file_path, label: translate('pim_import_export.form.job_instance.storage_form.file_path.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.file_path.placeholder', {
                file_extension: fileExtension,
            }), onChange: function (file_path) { return onStorageChange(__assign(__assign({}, storage), { file_path: file_path })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[file_path]') }),
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.region, label: translate('pim_import_export.form.job_instance.storage_form.region.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.region.placeholder'), onChange: function (region) { return onStorageChange(__assign(__assign({}, storage), { region: region })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[region]') }),
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.bucket, label: translate('pim_import_export.form.job_instance.storage_form.bucket.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.bucket.placeholder'), onChange: function (bucket) { return onStorageChange(__assign(__assign({}, storage), { bucket: bucket })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[bucket]') }),
        react_1.default.createElement(shared_1.TextField, { value: storage.key, required: true, label: translate('pim_import_export.form.job_instance.storage_form.key.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.key.placeholder'), onChange: function (key) { return onStorageChange(__assign(__assign({}, storage), { key: key })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[key]') }),
        react_1.default.createElement(shared_1.TextField, { actions: secretIsStoredOnServer && (react_1.default.createElement(akeneo_design_system_1.Button, { level: "secondary", ghost: true, size: "small", onClick: function () { return onStorageChange(__assign(__assign({}, storage), { secret: '' })); } }, translate('pim_common.edit'))), required: true, value: secretIsStoredOnServer ? '••••••••' : storage.secret, readOnly: secretIsStoredOnServer, type: "password", label: translate('pim_import_export.form.job_instance.storage_form.secret.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.secret.placeholder'), onChange: function (secret) { return onStorageChange(__assign(__assign({}, storage), { secret: secret })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[secret]') }),
        react_1.default.createElement(CheckStorageConnection_1.CheckStorageConnection, { jobInstanceCode: jobInstanceCode, storage: storage })));
};
exports.AmazonS3StorageConfigurator = AmazonS3StorageConfigurator;
//# sourceMappingURL=AmazonS3StorageConfigurator.js.map
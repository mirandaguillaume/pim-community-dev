"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.StorageForm = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var StorageConfigurator_1 = require("./StorageConfigurator");
var models_1 = require("../models");
var StorageForm = function (_a) {
    var jobInstanceCode = _a.jobInstanceCode, jobType = _a.jobType, fileExtension = _a.fileExtension, storage = _a.storage, validationErrors = _a.validationErrors, onStorageChange = _a.onStorageChange;
    var translate = (0, shared_1.useTranslate)();
    var featureFlags = (0, shared_1.useFeatureFlags)();
    var handleTypeChange = function (type) {
        return (0, models_1.isValidStorageType)(type, featureFlags) && onStorageChange((0, models_1.getDefaultStorage)(jobType, type, fileExtension));
    };
    var storageTypes = (0, models_1.getEnabledStorageTypes)(featureFlags);
    var StorageConfigurator = (0, StorageConfigurator_1.getStorageConfigurator)(storage.type, featureFlags);
    return (react_1.default.createElement(shared_1.Section, null,
        react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
            react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate('pim_import_export.form.job_instance.storage_form.title'))),
        react_1.default.createElement(akeneo_design_system_1.Field, { label: translate('pim_import_export.form.job_instance.storage_form.connection.label') },
            react_1.default.createElement(akeneo_design_system_1.SelectInput, { value: storage.type, onChange: handleTypeChange, emptyResultLabel: translate('pim_common.no_result'), openLabel: translate('pim_common.open'), clearable: false }, storageTypes.map(function (storageType) { return (react_1.default.createElement(akeneo_design_system_1.SelectInput.Option, { value: storageType, key: storageType }, translate("pim_import_export.form.job_instance.storage_form.connection." + storageType))); })),
            (0, shared_1.filterErrors)(validationErrors, '[type]').map(function (error, index) { return (react_1.default.createElement(akeneo_design_system_1.Helper, { key: index, inline: true, level: "error" }, translate(error.messageTemplate, error.parameters))); })),
        null !== StorageConfigurator && (react_1.default.createElement(StorageConfigurator, { jobInstanceCode: jobInstanceCode, storage: storage, fileExtension: fileExtension, validationErrors: validationErrors, onStorageChange: onStorageChange }))));
};
exports.StorageForm = StorageForm;
//# sourceMappingURL=StorageForm.js.map
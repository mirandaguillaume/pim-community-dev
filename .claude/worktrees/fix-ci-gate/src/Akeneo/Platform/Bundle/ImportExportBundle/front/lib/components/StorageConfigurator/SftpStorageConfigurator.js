"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
exports.SftpStorageConfigurator = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var hooks_1 = require("../../hooks/");
var models_1 = require("../../models");
var CheckStorageConnection_1 = require("./CheckStorageConnection");
var CopyableInputContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n"])));
var CopyableInput = styled_components_1.default.input(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  width: 100%;\n  height: 40px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  box-sizing: border-box;\n  background: ", ";\n  color: ", ";\n  font-size: ", ";\n  line-height: 40px;\n  padding: 0 35px 0 15px;\n  outline-style: none;\n  cursor: not-allowed;\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"], ["\n  width: 100%;\n  height: 40px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  box-sizing: border-box;\n  background: ", ";\n  color: ", ";\n  font-size: ", ";\n  line-height: 40px;\n  padding: 0 35px 0 15px;\n  outline-style: none;\n  cursor: not-allowed;\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"])), (0, akeneo_design_system_1.getColor)('grey', 80), (0, akeneo_design_system_1.getColor)('grey', 20), (0, akeneo_design_system_1.getColor)('grey', 100), (0, akeneo_design_system_1.getFontSize)('default'), (0, akeneo_design_system_1.getColor)('blue', 40), (0, akeneo_design_system_1.getColor)('grey', 100));
var CopyableIcon = (0, styled_components_1.default)(akeneo_design_system_1.CopyIcon)(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px;\n  color: ", ";\n  cursor: pointer;\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 12px;\n  color: ", ";\n  cursor: pointer;\n"])), (0, akeneo_design_system_1.getColor)('grey', 100));
var SftpStorageConfigurator = function (_a) {
    var _b, _c;
    var jobInstanceCode = _a.jobInstanceCode, storage = _a.storage, fileExtension = _a.fileExtension, validationErrors = _a.validationErrors, onStorageChange = _a.onStorageChange;
    if (!(0, models_1.isSftpStorage)(storage)) {
        throw new Error("Invalid storage type \"" + storage.type + "\" for sftp storage configurator");
    }
    var translate = (0, shared_1.useTranslate)();
    var portValidationErrors = (0, shared_1.filterErrors)(validationErrors, '[port]');
    var publicKey = (0, hooks_1.useGetPublicKey)();
    var passwordIsStoredOnServer = storage.login_type === 'password' && storage.password === undefined;
    var notify = (0, shared_1.useNotify)();
    var canCopyToClipboard = function () { return 'clipboard' in navigator; };
    var copyToClipboard = function (publicKey) {
        if (!canCopyToClipboard()) {
            return;
        }
        navigator.clipboard.writeText(publicKey);
        notify(shared_1.NotificationLevel.SUCCESS, translate('pim_import_export.form.job_instance.storage_form.public_key.copy_notification_success'));
    };
    var handleLoginTypeChange = function (newLoginType) {
        if ('password' === newLoginType) {
            onStorageChange(__assign(__assign({}, storage), { login_type: newLoginType, password: '' }));
        }
        else if ('private_key' === newLoginType) {
            var newStorage = __assign({}, storage);
            if ((0, models_1.isSftpPasswordStorage)(newStorage)) {
                delete newStorage.password;
            }
            onStorageChange(__assign(__assign({}, newStorage), { login_type: newLoginType }));
        }
    };
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.file_path, label: translate('pim_import_export.form.job_instance.storage_form.file_path.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.file_path.placeholder', {
                file_extension: fileExtension,
            }), onChange: function (file_path) { return onStorageChange(__assign(__assign({}, storage), { file_path: file_path })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[file_path]') }),
        react_1.default.createElement(shared_1.TextField, { required: true, value: storage.host, label: translate('pim_import_export.form.job_instance.storage_form.host.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.host.placeholder'), onChange: function (host) { return onStorageChange(__assign(__assign({}, storage), { host: host })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[host]') }),
        react_1.default.createElement(shared_1.TextField, { required: false, value: (_b = storage.fingerprint) !== null && _b !== void 0 ? _b : '', label: translate('pim_import_export.form.job_instance.storage_form.fingerprint.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.fingerprint.placeholder'), onChange: function (fingerprint) {
                return onStorageChange(__assign(__assign({}, storage), { fingerprint: '' === fingerprint ? undefined : fingerprint }));
            }, errors: (0, shared_1.filterErrors)(validationErrors, '[fingerprint]') },
            react_1.default.createElement(akeneo_design_system_1.Helper, null, translate('pim_import_export.form.job_instance.storage_form.fingerprint.helper'))),
        react_1.default.createElement(akeneo_design_system_1.Field, { label: translate('pim_import_export.form.job_instance.storage_form.port.label') + " " + translate('pim_common.required_label') },
            react_1.default.createElement(akeneo_design_system_1.NumberInput, { min: 1, max: 65535, onChange: function (port) { return onStorageChange(__assign(__assign({}, storage), { port: parseInt(port, 10) })); }, invalid: 0 < portValidationErrors.length, value: storage.port.toString(), placeholder: translate('pim_import_export.form.job_instance.storage_form.port.placeholder') }),
            portValidationErrors.map(function (error, key) { return (react_1.default.createElement(akeneo_design_system_1.Helper, { key: key, level: "error", inline: true }, translate(error.messageTemplate, error.parameters, error.plural))); })),
        react_1.default.createElement(akeneo_design_system_1.Field, { label: translate('pim_import_export.form.job_instance.storage_form.login_type.label') },
            react_1.default.createElement(akeneo_design_system_1.SelectInput, { value: storage.login_type, onChange: function (login_type) {
                    if ((0, models_1.isValidSftpLoginType)(login_type)) {
                        handleLoginTypeChange(login_type);
                    }
                }, emptyResultLabel: translate('pim_common.no_result'), openLabel: translate('pim_common.open'), clearable: false }, models_1.SFTP_STORAGE_LOGIN_TYPES.map(function (loginType) { return (react_1.default.createElement(akeneo_design_system_1.SelectInput.Option, { value: loginType, key: loginType }, translate("pim_import_export.form.job_instance.storage_form.login_type." + loginType))); }))),
        react_1.default.createElement(shared_1.TextField, { value: storage.username, required: true, label: translate('pim_import_export.form.job_instance.storage_form.username.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.username.placeholder'), onChange: function (username) { return onStorageChange(__assign(__assign({}, storage), { username: username })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[username]') }),
        storage.login_type === 'password' ? (react_1.default.createElement(shared_1.TextField, { actions: passwordIsStoredOnServer && (react_1.default.createElement(akeneo_design_system_1.Button, { level: "secondary", ghost: true, size: "small", onClick: function () { return onStorageChange(__assign(__assign({}, storage), { password: '' })); } }, translate('pim_common.edit'))), value: passwordIsStoredOnServer ? '••••••••' : (_c = storage.password) !== null && _c !== void 0 ? _c : '', readOnly: passwordIsStoredOnServer, required: true, type: "password", label: translate('pim_import_export.form.job_instance.storage_form.password.label'), placeholder: translate('pim_import_export.form.job_instance.storage_form.password.placeholder'), onChange: function (password) { return onStorageChange(__assign(__assign({}, storage), { password: password })); }, errors: (0, shared_1.filterErrors)(validationErrors, '[password]') })) : (react_1.default.createElement(akeneo_design_system_1.Field, { label: translate('pim_import_export.form.job_instance.storage_form.public_key.label') },
            react_1.default.createElement(CopyableInputContainer, null,
                react_1.default.createElement(CopyableInput, { disabled: true, "data-testid": "publicKey", value: publicKey !== null && publicKey !== void 0 ? publicKey : '' }),
                react_1.default.createElement(CopyableIcon, { title: translate('pim_import_export.form.job_instance.storage_form.public_key.button_title'), size: 16, "data-testid": "copyToClipboard", onClick: function () { return publicKey && copyToClipboard(publicKey); } })))),
        react_1.default.createElement(CheckStorageConnection_1.CheckStorageConnection, { jobInstanceCode: jobInstanceCode, storage: storage })));
};
exports.SftpStorageConfigurator = SftpStorageConfigurator;
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=SftpStorageConfigurator.js.map
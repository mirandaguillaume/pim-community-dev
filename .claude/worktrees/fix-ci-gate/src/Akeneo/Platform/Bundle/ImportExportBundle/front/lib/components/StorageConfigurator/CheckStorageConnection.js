"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.CheckStorageConnection = void 0;
var react_1 = __importDefault(require("react"));
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var hooks_1 = require("../../hooks");
var Form = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  gap: 5px;\n"], ["\n  display: flex;\n  flex-direction: column;\n  gap: 5px;\n"])));
var Field = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  gap: 8.5px;\n  color: ", ";\n"], ["\n  display: flex;\n  align-items: center;\n  gap: 8.5px;\n  color: ", ";\n"])), (0, akeneo_design_system_1.getColor)('green', 100));
var CheckStorageConnection = function (_a) {
    var jobInstanceCode = _a.jobInstanceCode, storage = _a.storage;
    var translate = (0, shared_1.useTranslate)();
    var _b = (0, hooks_1.useCheckStorageConnection)(jobInstanceCode, storage), isValid = _b[0], canCheckConnection = _b[1], checkReliability = _b[2];
    return (react_1.default.createElement(Form, null,
        react_1.default.createElement(Field, null,
            react_1.default.createElement(akeneo_design_system_1.Button, { onClick: checkReliability, disabled: !canCheckConnection, level: "primary" }, translate('pim_import_export.form.job_instance.connection_checker.label')),
            isValid && react_1.default.createElement(akeneo_design_system_1.CheckIcon, null)),
        false === isValid && (react_1.default.createElement(akeneo_design_system_1.Helper, { inline: true, level: "error" }, translate('pim_import_export.form.job_instance.connection_checker.exception')))));
};
exports.CheckStorageConnection = CheckStorageConnection;
var templateObject_1, templateObject_2;
//# sourceMappingURL=CheckStorageConnection.js.map
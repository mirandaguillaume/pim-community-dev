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
var __read = (this && this.__read) || function (o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.EditPermissionsForm = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var styled_components_1 = __importDefault(require("styled-components"));
var FormContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"], ["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"])));
var PermissionField = (0, styled_components_1.default)(akeneo_design_system_1.Field)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  max-width: 400px;\n"], ["\n  max-width: 400px;\n"])));
var EditPermissionsForm = function (_a) {
    var formData = _a.formData, onChangePermissions = _a.onChangePermissions, onChangeApplyPermissionsOnChildren = _a.onChangeApplyPermissionsOnChildren;
    var translate = (0, shared_1.useTranslate)();
    if (formData === null || !formData.permissions) {
        return (0, jsx_runtime_1.jsx)(jsx_runtime_1.Fragment, {}, void 0);
    }
    return ((0, jsx_runtime_1.jsxs)(FormContainer, { children: [(0, jsx_runtime_1.jsx)(PermissionField, __assign({ label: translate('category.permissions.view.label') }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput, __assign({ readOnly: false, value: formData.permissions.view.value, name: formData.permissions.view.fullName, emptyResultLabel: translate('pim_common.no_result'), openLabel: translate('pim_common.open'), removeLabel: translate('pim_common.remove'), onChange: function (changedValues) { return onChangePermissions('view', changedValues); } }, { children: Object.entries(formData.permissions.view.choices).map(function (_a) {
                        var _b = __read(_a, 2), key = _b[0], choice = _b[1];
                        return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput.Option, __assign({ value: choice.value }, { children: choice.label }), "view-" + key));
                    }) }), void 0) }), void 0), (0, jsx_runtime_1.jsx)(PermissionField, __assign({ label: translate('category.permissions.edit.label') }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput, __assign({ value: formData.permissions.edit.value, name: formData.permissions.edit.fullName, emptyResultLabel: translate('pim_common.no_result'), openLabel: translate('pim_common.open'), removeLabel: translate('pim_common.remove'), onChange: function (changedValues) { return onChangePermissions('edit', changedValues); } }, { children: Object.entries(formData.permissions.edit.choices).map(function (_a) {
                        var _b = __read(_a, 2), key = _b[0], choice = _b[1];
                        return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput.Option, __assign({ value: choice.value }, { children: choice.label }), "edit-" + key));
                    }) }), void 0) }), void 0), (0, jsx_runtime_1.jsxs)(PermissionField, __assign({ label: translate('category.permissions.own.label') }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput, __assign({ value: formData.permissions.own.value, name: formData.permissions.own.fullName, emptyResultLabel: translate('pim_common.no_result'), openLabel: translate('pim_common.open'), removeLabel: translate('pim_common.remove'), onChange: function (changedValues) { return onChangePermissions('own', changedValues); } }, { children: Object.entries(formData.permissions.own.choices).map(function (_a) {
                            var _b = __read(_a, 2), key = _b[0], choice = _b[1];
                            return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.MultiSelectInput.Option, __assign({ value: choice.value }, { children: choice.label }), "own-" + key));
                        }) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Helper, __assign({ level: "info" }, { children: translate('category.permissions.own.help') }), void 0)] }), void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Field, __assign({ label: translate('category.permissions.apply_on_children.label') }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.BooleanInput, { clearable: false, readOnly: false, value: formData.permissions.apply_on_children.value === '1', noLabel: translate('pim_common.no'), yesLabel: translate('pim_common.yes'), onChange: function (changedValue) { return onChangeApplyPermissionsOnChildren(changedValue); } }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Helper, __assign({ level: "info" }, { children: translate('category.permissions.apply_on_children.help') }), void 0)] }), void 0)] }, void 0));
};
exports.EditPermissionsForm = EditPermissionsForm;
var templateObject_1, templateObject_2;
//# sourceMappingURL=EditPermissionsForm.js.map
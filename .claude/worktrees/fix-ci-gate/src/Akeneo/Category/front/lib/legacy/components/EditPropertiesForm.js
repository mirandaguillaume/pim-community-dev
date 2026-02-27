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
exports.EditPropertiesForm = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var styled_components_1 = __importDefault(require("styled-components"));
var FormContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"], ["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"])));
var ErrorMessage = (0, styled_components_1.default)(akeneo_design_system_1.Helper)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  margin: 20px 0 0 0;\n"], ["\n  margin: 20px 0 0 0;\n"])));
var EditPropertiesForm = function (_a) {
    var category = _a.category, formData = _a.formData, onChangeLabel = _a.onChangeLabel;
    var translate = (0, shared_1.useTranslate)();
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    if (formData === null) {
        return (0, jsx_runtime_1.jsx)(jsx_runtime_1.Fragment, {}, void 0);
    }
    return ((0, jsx_runtime_1.jsxs)(FormContainer, { children: [formData.errors.map(function (errorMessage, key) {
                return ((0, jsx_runtime_1.jsx)(ErrorMessage, __assign({ level: "error" }, { children: errorMessage }), "error-" + key));
            }), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, { children: translate('pim_common.code') }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Field, __assign({ label: translate('pim_common.code'), requiredLabel: translate('pim_common.required_label') }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { name: "code", readOnly: true, value: category.code }, void 0) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, { children: translate('pim_common.label') }, void 0) }, void 0), Object.entries(formData.label).map(function (_a) {
                var _b = __read(_a, 2), locale = _b[0], labelField = _b[1];
                return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Field, __assign({ label: labelField.label }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { name: labelField.fullName, readOnly: !isGranted('pim_enrich_product_category_edit'), onChange: function (changedLabel) { return onChangeLabel(locale, changedLabel); }, value: labelField.value }, void 0) }), locale));
            })] }, void 0));
};
exports.EditPropertiesForm = EditPropertiesForm;
var templateObject_1, templateObject_2;
//# sourceMappingURL=EditPropertiesForm.js.map
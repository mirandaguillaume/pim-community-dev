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
exports.EditTemplatePropertiesForm = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var styled_components_1 = __importDefault(require("styled-components"));
var useUiLocales_1 = require("../../../hooks/useUiLocales");
var TemplateLabelTranslationInput_1 = require("./TemplateLabelTranslationInput");
var EditTemplatePropertiesForm = function (_a) {
    var template = _a.template;
    var translate = (0, shared_1.useTranslate)();
    var uiLocales = (0, useUiLocales_1.useUiLocales)();
    var sortedUiLocales = uiLocales === null || uiLocales === void 0 ? void 0 : uiLocales.sort(function (a, b) { return a.label.localeCompare(b.label); });
    return ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, { children: translate('akeneo.category.template.properties.general_properties') }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(FieldContainer, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Field, __assign({ label: translate('pim_common.code') }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { value: template.code, readOnly: true }, void 0) }), void 0) }, void 0), (0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, { children: translate('akeneo.category.template.properties.label_translations_in_ui_locales') }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(FieldContainer, { children: sortedUiLocales ? (sortedUiLocales.map(function (locale) { return ((0, jsx_runtime_1.jsx)(TemplateLabelTranslationInput_1.TemplateLabelTranslationInput, { locale: locale, template: template }, locale.code)); })) : ((0, jsx_runtime_1.jsx)(InputListSkeleton, {}, void 0)) }, void 0)] }, void 0)] }, void 0));
};
exports.EditTemplatePropertiesForm = EditTemplatePropertiesForm;
var FieldContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-bottom: 15px;\n  & > * {\n    margin: 10px 0 0 0;\n  }\n"], ["\n  margin-bottom: 15px;\n  & > * {\n    margin: 10px 0 0 0;\n  }\n"])));
var InputListSkeleton = function () { return ((0, jsx_runtime_1.jsx)(jsx_runtime_1.Fragment, { children: Array(15)
        .fill(0)
        .map(function (_, i) { return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.SkeletonPlaceholder, __assign({ style: { maxWidth: 460 } }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Field, __assign({ label: "label" }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { readOnly: true }, void 0) }), void 0) }), i)); }) }, void 0)); };
var templateObject_1;
//# sourceMappingURL=EditTemplatePropertiesForm.js.map
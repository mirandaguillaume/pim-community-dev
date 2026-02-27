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
exports.AttributeSettings = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var styled_components_1 = __importDefault(require("styled-components"));
var useCatalogActivatedLocaleCodes_1 = require("../../../hooks/useCatalogActivatedLocaleCodes");
var useCatalogLocales_1 = require("../../../hooks/useCatalogLocales");
var attributes_1 = require("../../attributes");
var DeactivateTemplateAttributeModal_1 = require("../DeactivateTemplateAttributeModal");
var AttributeLabelTranslationInput_1 = require("./AttributeLabelTranslationInput");
var AttributeOptionRichTextCheckbox_1 = require("./AttributeOptionRichTextCheckbox");
var AttributeSettings = function (_a) {
    var attribute = _a.attribute;
    var translate = (0, shared_1.useTranslate)();
    var attributeLabel = (0, attributes_1.getLabelFromAttribute)(attribute, shared_1.userContext.get('catalogLocale'));
    var catalogLocales = (0, useCatalogLocales_1.useCatalogLocales)();
    var activatedCatalogLocaleCodes = (0, useCatalogActivatedLocaleCodes_1.useCatalogActivatedLocaleCodes)();
    var sortedActivatedCatalogLocales = activatedCatalogLocaleCodes === null || activatedCatalogLocaleCodes === void 0 ? void 0 : activatedCatalogLocaleCodes.map(function (localeCode) { return (catalogLocales === null || catalogLocales === void 0 ? void 0 : catalogLocales.find(function (locale) { return locale.code === localeCode; })) || { code: localeCode, label: localeCode }; }).sort(function (a, b) { return a.label.localeCompare(b.label); });
    var _b = __read((0, akeneo_design_system_1.useBooleanState)(false), 3), isDeactivateTemplateAttributeModalOpen = _b[0], openDeactivateTemplateAttributeModal = _b[1], closeDeactivateTemplateAttributeModal = _b[2];
    return ((0, jsx_runtime_1.jsxs)(SettingsContainer, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, __assign({ sticky: 0 }, { children: (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.SectionTitle.Title, { children: [attributeLabel, " ", translate('akeneo.category.template.attribute.settings.title')] }, void 0) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, __assign({ level: "secondary" }, { children: translate('akeneo.category.template.attribute.settings.options.title') }), void 0) }, void 0), (0, jsx_runtime_1.jsxs)(FieldContainer, { children: [(0, jsx_runtime_1.jsx)(AttributeOptionRichTextCheckbox_1.AttributeOptionRichTextCheckbox, { attribute: attribute }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Checkbox, __assign({ checked: attribute.is_localizable, readOnly: true }, { children: translate('akeneo.category.template.attribute.settings.options.value_per_locale') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Checkbox, __assign({ checked: attribute.is_scopable, readOnly: true }, { children: translate('akeneo.category.template.attribute.settings.options.value_per_channel') }), void 0)] }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, __assign({ level: "secondary" }, { children: translate('akeneo.category.template.attribute.settings.translations.title') }), void 0) }, void 0), (0, jsx_runtime_1.jsx)(FieldContainer, { children: sortedActivatedCatalogLocales === null || sortedActivatedCatalogLocales === void 0 ? void 0 : sortedActivatedCatalogLocales.map(function (locale) { return ((0, jsx_runtime_1.jsx)(AttributeLabelTranslationInput_1.AttributeLabelTranslationInput, { attribute: attribute, localeCode: locale.code, label: locale.label }, locale.code)); }) }, void 0), (0, jsx_runtime_1.jsxs)(Footer, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ level: "danger", ghost: true, onClick: openDeactivateTemplateAttributeModal }, { children: translate('akeneo.category.template.attribute.delete_button') }), void 0), isDeactivateTemplateAttributeModalOpen && ((0, jsx_runtime_1.jsx)(DeactivateTemplateAttributeModal_1.DeactivateTemplateAttributeModal, { templateUuid: attribute.template_uuid, attribute: { uuid: attribute.uuid, label: attributeLabel }, onClose: closeDeactivateTemplateAttributeModal }, void 0))] }, void 0)] }, void 0));
};
exports.AttributeSettings = AttributeSettings;
var SettingsContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  padding-left: 40px;\n  width: 510px;\n  overflow-y: auto;\n"], ["\n  display: flex;\n  flex-direction: column;\n  padding-left: 40px;\n  width: 510px;\n  overflow-y: auto;\n"])));
var FieldContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  margin-bottom: 15px;\n  & > * {\n    margin: 10px 0 0 0;\n  }\n"], ["\n  margin-bottom: 15px;\n  & > * {\n    margin: 10px 0 0 0;\n  }\n"])));
var Footer = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: row-reverse;\n  padding: 5px 0 5px;\n  margin-top: 5px;\n  position: sticky;\n  bottom: 0;\n  background-color: #ffffff;\n"], ["\n  display: flex;\n  flex-direction: row-reverse;\n  padding: 5px 0 5px;\n  margin-top: 5px;\n  position: sticky;\n  bottom: 0;\n  background-color: #ffffff;\n"])));
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=AttributeSettings.js.map
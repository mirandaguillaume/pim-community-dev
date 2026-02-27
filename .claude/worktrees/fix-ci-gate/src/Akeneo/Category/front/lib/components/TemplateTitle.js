"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TemplateTitle = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var TemplateTitleContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 400;\n  line-height: 16px;\n  text-transform: uppercase;\n  margin-top: 20px;\n"], ["\n  color: ", ";\n  font-size: ", ";\n  font-weight: 400;\n  line-height: 16px;\n  text-transform: uppercase;\n  margin-top: 20px;\n"])), (0, akeneo_design_system_1.getColor)('grey', 120), (0, akeneo_design_system_1.getFontSize)('default'));
var TemplateTitleLabel = styled_components_1.default.span(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), (0, akeneo_design_system_1.getColor)('grey', 140));
TemplateTitleContainer.displayName = 'TemplateTitleContainer';
TemplateTitleLabel.displayName = 'TemplateTitleLabel';
var getLabelFromTemplate = function (template, locale) {
    var _a;
    return (_a = template.labels[locale]) !== null && _a !== void 0 ? _a : '[' + template.code + ']';
};
var TemplateTitle = function (_a) {
    var template = _a.template, locale = _a.locale;
    var translate = (0, shared_1.useTranslate)();
    var userContext = (0, shared_1.useUserContext)();
    var catalogLocale = locale ? locale : userContext.get('catalogLocale');
    return ((0, jsx_runtime_1.jsxs)(TemplateTitleContainer, { children: [translate('akeneo.category.edition_form.template.title'), " :", ' ', (0, jsx_runtime_1.jsx)(TemplateTitleLabel, { children: getLabelFromTemplate(template, catalogLocale) }, void 0)] }, void 0));
};
exports.TemplateTitle = TemplateTitle;
var templateObject_1, templateObject_2;
//# sourceMappingURL=TemplateTitle.js.map
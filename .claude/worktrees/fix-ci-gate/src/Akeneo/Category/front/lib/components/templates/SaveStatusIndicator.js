"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.SaveStatusIndicator = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var styled_components_1 = __importDefault(require("styled-components"));
var useSaveStatus_1 = require("../../hooks/useSaveStatus");
var SaveStatusProvider_1 = require("../providers/SaveStatusProvider");
var SaveStatusIndicator = function () {
    var saveStatus = (0, useSaveStatus_1.useSaveStatus)();
    var translate = (0, shared_1.useTranslate)();
    switch (saveStatus.globalStatus) {
        case SaveStatusProvider_1.Status.EDITING:
            return ((0, jsx_runtime_1.jsxs)(IndicatorContainer, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.EditIcon, { color: "#a1a9b7", size: 24 }, void 0), (0, jsx_runtime_1.jsx)(TextGrey, { children: translate('akeneo.category.template.auto-save.editing') }, void 0)] }, void 0));
        case SaveStatusProvider_1.Status.SAVING:
            return ((0, jsx_runtime_1.jsxs)(IndicatorContainer, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.LoaderIcon, { color: "#a1a9b7", size: 24 }, void 0), (0, jsx_runtime_1.jsx)(TextGrey, { children: translate('akeneo.category.template.auto-save.saving') }, void 0)] }, void 0));
        case SaveStatusProvider_1.Status.ERRORS:
            return ((0, jsx_runtime_1.jsxs)(IndicatorContainer, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.DangerIcon, { color: "#f9b53f", size: 24 }, void 0), (0, jsx_runtime_1.jsx)(TextBlack, { children: translate('akeneo.category.template.auto-save.errors') }, void 0)] }, void 0));
        case SaveStatusProvider_1.Status.SAVED:
        default:
            return ((0, jsx_runtime_1.jsxs)(IndicatorContainer, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.CheckRoundIcon, { color: "#67b373", size: 24 }, void 0), (0, jsx_runtime_1.jsx)(TextBlack, { children: translate('akeneo.category.template.auto-save.saved') }, void 0)] }, void 0));
    }
};
exports.SaveStatusIndicator = SaveStatusIndicator;
var IndicatorContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  margin-right: 20px;\n"], ["\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  margin-right: 20px;\n"])));
var TextBlack = styled_components_1.default.p(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  margin-left: 10px;\n  margin-right: 10px;\n  color: #11324d;\n"], ["\n  margin-left: 10px;\n  margin-right: 10px;\n  color: #11324d;\n"])));
var TextGrey = styled_components_1.default.p(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  margin-left: 10px;\n  margin-right: 10px;\n  color: #67768a;\n"], ["\n  margin-left: 10px;\n  margin-right: 10px;\n  color: #67768a;\n"])));
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=SaveStatusIndicator.js.map
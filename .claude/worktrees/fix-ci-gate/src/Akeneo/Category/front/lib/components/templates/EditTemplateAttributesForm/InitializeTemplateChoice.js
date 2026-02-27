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
exports.InitializeTemplateChoice = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var AddTemplateAttributeModal_1 = require("../AddTemplateAttributeModal");
var styled_components_1 = __importDefault(require("styled-components"));
var LoadAttributeSetModal_1 = require("./LoadAttributeSetModal");
var useTrackUsageOfLoadPredefinedAttributes_1 = require("../../../hooks/useTrackUsageOfLoadPredefinedAttributes");
var InitializeTemplateChoice = function (_a) {
    var templateId = _a.templateId;
    var trackUsageOfLoadPredefinedAttributes = (0, useTrackUsageOfLoadPredefinedAttributes_1.useTrackUsageOfLoadPredefinedAttributes)(templateId);
    var _b = __read((0, akeneo_design_system_1.useBooleanState)(false), 3), isAddTemplateAttributeModalOpen = _b[0], openAddTemplateAttributeModal = _b[1], closeAddTemplateAttributeModal = _b[2];
    var _c = __read((0, akeneo_design_system_1.useBooleanState)(false), 3), isLoadAttributeSetModalOpen = _c[0], openLoadAttributeSetModalOpen = _c[1], closeLoadAttributeSetModalOpen = _c[2];
    return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Placeholder, __assign({ title: (0, shared_1.translate)('akeneo.category.template.initialize.title'), illustration: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.AttributesIllustration, {}, void 0), size: "large" }, { children: [(0, jsx_runtime_1.jsxs)(TextContainer, { children: [(0, shared_1.translate)('akeneo.category.template.initialize.message'), (0, jsx_runtime_1.jsx)("br", {}, void 0), (0, shared_1.translate)('akeneo.category.template.initialize.choice_message')] }, void 0), (0, jsx_runtime_1.jsxs)(ButtonContainer, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ level: "tertiary", ghost: true, onClick: openLoadAttributeSetModalOpen }, { children: (0, shared_1.translate)('akeneo.category.template.initialize.button.load') }), void 0), isLoadAttributeSetModalOpen && ((0, jsx_runtime_1.jsx)(LoadAttributeSetModal_1.LoadAttributeSetModal, { templateId: templateId, onClose: closeLoadAttributeSetModalOpen, onSuccess: function () { return trackUsageOfLoadPredefinedAttributes('load_predefined_attributes'); } }, void 0)), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ level: "primary", onClick: openAddTemplateAttributeModal }, { children: (0, shared_1.translate)('akeneo.category.template.initialize.button.create') }), void 0), isAddTemplateAttributeModalOpen && ((0, jsx_runtime_1.jsx)(AddTemplateAttributeModal_1.AddTemplateAttributeModal, { templateId: templateId, onClose: closeAddTemplateAttributeModal, onSuccess: function () { return trackUsageOfLoadPredefinedAttributes('create_first_attribute'); } }, void 0))] }, void 0)] }), void 0));
};
exports.InitializeTemplateChoice = InitializeTemplateChoice;
var TextContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  text-align: center;\n"], ["\n  text-align: center;\n"])));
var ButtonContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n"], ["\n  display: flex;\n  gap: 10px;\n"])));
var templateObject_1, templateObject_2;
//# sourceMappingURL=InitializeTemplateChoice.js.map
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
exports.DeactivateTemplateModal = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var styled_components_1 = __importDefault(require("styled-components"));
var useDeactivateTemplate_1 = require("../../hooks/useDeactivateTemplate");
var Content = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  padding-bottom: 20px;\n"], ["\n  padding-bottom: 20px;\n"])));
var DeactivateTemplateModal = function (_a) {
    var template = _a.template, onClose = _a.onClose;
    var translate = (0, shared_1.useTranslate)();
    var deactivateTemplate = (0, useDeactivateTemplate_1.useDeactivateTemplate)(template);
    var handleDeactivateTemplate = function () { return deactivateTemplate(); };
    return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Modal, __assign({ illustration: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.DeleteIllustration, {}, void 0), onClose: onClose, closeTitle: translate('pim_common.close') }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Modal.SectionTitle, __assign({ color: "brand" }, { children: translate('akeneo.category.template.deactivate.deactivate_template') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Modal.Title, { children: translate('akeneo.category.template.deactivate.confirmation_modal.title') }, void 0), (0, jsx_runtime_1.jsx)(Content, { children: translate('akeneo.category.template.deactivate.confirmation_modal.message', { template: template.label }) }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Helper, __assign({ level: "error" }, { children: translate('akeneo.category.template.deactivate.confirmation_modal.helper') }), void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Modal.BottomButtons, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ level: "tertiary", onClick: onClose }, { children: translate('pim_common.cancel') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ level: "danger", onClick: handleDeactivateTemplate }, { children: translate('akeneo.category.template.deactivate.confirmation_modal.deactivate') }), void 0)] }, void 0)] }), void 0));
};
exports.DeactivateTemplateModal = DeactivateTemplateModal;
var templateObject_1;
//# sourceMappingURL=DeactivateTemplateModal.js.map
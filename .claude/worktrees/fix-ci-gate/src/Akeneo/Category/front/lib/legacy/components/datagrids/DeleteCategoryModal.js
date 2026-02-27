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
exports.DeleteCategoryModal = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var styled_components_1 = __importDefault(require("styled-components"));
var DeleteCategoryModal = function (_a) {
    var categoryLabel = _a.categoryLabel, closeModal = _a.closeModal, deleteCategory = _a.deleteCategory, message = _a.message;
    var translate = (0, shared_1.useTranslate)();
    return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Modal, __assign({ closeTitle: "Close", onClose: closeModal, illustration: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.DeleteIllustration, {}, void 0) }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Modal.SectionTitle, __assign({ color: "brand" }, { children: translate('pim_enrich.entity.category.plural_label') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Modal.Title, { children: translate('pim_common.confirm_deletion') }, void 0), (0, jsx_runtime_1.jsx)(Message, { children: translate(message, { name: categoryLabel }) }, void 0), (0, jsx_runtime_1.jsxs)(ActionButtons, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ onClick: closeModal, level: "tertiary" }, { children: translate('pim_common.cancel') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ onClick: function () { return deleteCategory(); }, level: "danger", className: "ok" }, { children: translate('pim_common.delete') }), void 0)] }, void 0)] }), void 0));
};
exports.DeleteCategoryModal = DeleteCategoryModal;
var Message = styled_components_1.default.p(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  font-size: ", ";\n"], ["\n  font-size: ", ";\n"])), (0, akeneo_design_system_1.getFontSize)('big'));
var ActionButtons = styled_components_1.default.p(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  margin-top: 15px;\n\n  button:first-child {\n    margin-right: 10px;\n  }\n"], ["\n  margin-top: 15px;\n\n  button:first-child {\n    margin-right: 10px;\n  }\n"])));
var templateObject_1, templateObject_2;
//# sourceMappingURL=DeleteCategoryModal.js.map
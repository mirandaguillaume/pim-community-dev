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
exports.CreateTemplateModal = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var user_context_1 = require("@akeneo-pim-community/shared/lib/dependencies/user-context");
var akeneo_design_system_1 = require("akeneo-design-system");
var react_1 = require("react");
var styled_components_1 = __importDefault(require("styled-components"));
var useCreateTemplate_1 = require("../../hooks/useCreateTemplate");
var apiFetch_1 = require("../../tools/apiFetch");
var CreateTemplateModal = function (_a) {
    var categoryTree = _a.categoryTree, onClose = _a.onClose;
    var defaultUserUiLocale = user_context_1.userContext.get('user_default_locale');
    var notify = (0, shared_1.useNotify)();
    var router = (0, shared_1.useRouter)();
    var _b = __read((0, react_1.useState)({ label: '', code: '' }), 2), form = _b[0], setForm = _b[1];
    var mutation = (0, useCreateTemplate_1.useCreateTemplate)();
    var handleCreate = function () {
        mutation.mutate({
            categoryTreeId: categoryTree.id,
            code: form.code,
            locale: defaultUserUiLocale,
            label: form.label,
        }, {
            onError: function (error) {
                if (error instanceof apiFetch_1.BadRequestError) {
                    return;
                }
                onClose();
                notify(shared_1.NotificationLevel.ERROR, (0, shared_1.translate)('akeneo.category.template.notification_error'));
            },
            onSuccess: function (data) {
                router.redirect(router.generate('pim_category_template_edit', {
                    treeId: categoryTree.id,
                    templateUuid: data.template_uuid,
                }));
            },
        });
    };
    var displayError = function (errorMessages) {
        return errorMessages.map(function (message) { return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Helper, __assign({ level: "error" }, { children: message }), message)); });
    };
    var error = null;
    if (mutation.error instanceof apiFetch_1.BadRequestError) {
        error = mutation.error.data;
    }
    return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Modal, __assign({ illustration: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.ProductCategoryIllustration, {}, void 0), onClose: onClose, closeTitle: (0, shared_1.translate)('pim_common.close') }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Modal.SectionTitle, __assign({ color: "brand" }, { children: (0, shared_1.translate)('akeneo.category.template.create') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Modal.Title, { children: (0, shared_1.translate)('akeneo.category.template.create_confirmation_modal.title') }, void 0), (0, jsx_runtime_1.jsx)(Content, { children: (0, jsx_runtime_1.jsxs)(FieldSet, { children: [(0, jsx_runtime_1.jsxs)(HelperContent, __assign({ level: "info" }, { children: [(0, shared_1.translate)('akeneo.category.template.create_confirmation_modal.helper'), ' ', (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Link, __assign({ href: "https://help.akeneo.com/serenity-take-the-power-over-your-products/serenity-enrich-your-category", target: "_blank" }, { children: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.link') }), void 0)] }), void 0), (0, jsx_runtime_1.jsxs)(CategoryTreeContainer, { children: [(0, jsx_runtime_1.jsx)("div", { children: (0, shared_1.translate)('akeneo.category.template.create_confirmation_modal.category_tree') }, void 0), (0, jsx_runtime_1.jsx)(TreeLabel, { children: categoryTree.label }, void 0)] }, void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Field, __assign({ label: (0, shared_1.translate)('pim_common.label'), locale: defaultUserUiLocale }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { value: form.label, invalid: !!(error === null || error === void 0 ? void 0 : error.labels), onChange: function (label) {
                                        setForm(__assign(__assign({}, form), { label: label }));
                                    } }, void 0), (error === null || error === void 0 ? void 0 : error.labels) && error.labels[defaultUserUiLocale] && displayError(error.labels[defaultUserUiLocale])] }), void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Field, __assign({ label: (0, shared_1.translate)('pim_common.code'), requiredLabel: (0, shared_1.translate)('pim_common.required_label') }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { value: form.code, invalid: !!(error === null || error === void 0 ? void 0 : error.templateCode), onChange: function (code) {
                                        setForm(__assign(__assign({}, form), { code: code }));
                                    } }, void 0), (error === null || error === void 0 ? void 0 : error.templateCode) && displayError(error.templateCode)] }), void 0)] }, void 0) }, void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Modal.BottomButtons, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ level: "tertiary", onClick: onClose }, { children: (0, shared_1.translate)('pim_common.cancel') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ disabled: mutation.isLoading, level: "primary", onClick: handleCreate }, { children: (0, shared_1.translate)('akeneo.category.template.add_attribute.confirmation_modal.create') }), void 0)] }, void 0)] }), void 0));
};
exports.CreateTemplateModal = CreateTemplateModal;
var Content = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  padding-bottom: 20px;\n"], ["\n  padding-bottom: 20px;\n"])));
var FieldSet = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  & > * {\n    margin-top: 20px;\n  }\n"], ["\n  & > * {\n    margin-top: 20px;\n  }\n"])));
var HelperContent = (0, styled_components_1.default)(akeneo_design_system_1.Helper)(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  min-width: 200px;\n  max-width: 460px;\n"], ["\n  min-width: 200px;\n  max-width: 460px;\n"])));
var CategoryTreeContainer = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  flex-direction: column;\n  align-items: flex-start;\n"], ["\n  flex-direction: column;\n  align-items: flex-start;\n"])));
var TreeLabel = styled_components_1.default.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: #000000;\n"], ["\n  color: #000000;\n"])));
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=CreateTemplateModal.js.map
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
exports.TemplatePage = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var react_router_1 = require("react-router");
var styled_components_1 = __importDefault(require("styled-components"));
var TemplateFormProvider_1 = require("../components/providers/TemplateFormProvider");
var DeactivateTemplateModal_1 = require("../components/templates/DeactivateTemplateModal");
var EditTemplateAttributesForm_1 = require("../components/templates/EditTemplateAttributesForm/EditTemplateAttributesForm");
var EditTemplatePropertiesForm_1 = require("../components/templates/EditTemplatePropertiesForm/EditTemplatePropertiesForm");
var SaveStatusIndicator_1 = require("../components/templates/SaveStatusIndicator");
var TemplateOtherActions_1 = require("../components/templates/TemplateOtherActions");
var hooks_1 = require("../hooks");
var Tabs;
(function (Tabs) {
    Tabs["ATTRIBUTE"] = "#pim_enrich-category-tab-attribute";
    Tabs["PROPERTY"] = "#pim_enrich-category-tab-property";
})(Tabs || (Tabs = {}));
var useTabInError = function () {
    var _a;
    var _b = __read((0, TemplateFormProvider_1.useTemplateForm)(), 1), state = _b[0];
    return _a = {},
        _a[Tabs.ATTRIBUTE] = Object.values(state.attributes).some(function (attribute) {
            return Object.values(attribute.labels || {}).some(function (_a) {
                var errors = _a.errors;
                return errors.length > 0;
            });
        }),
        _a[Tabs.PROPERTY] = Object.values(state.properties.labels).some(function (_a) {
            var errors = _a.errors;
            return errors.length > 0;
        }),
        _a;
};
var TemplatePage = function () {
    var router = (0, shared_1.useRouter)();
    var translate = (0, shared_1.useTranslate)();
    var userContext = (0, shared_1.useUserContext)();
    var _a = __read((0, shared_1.useSessionStorageState)(Tabs.ATTRIBUTE, 'pim_category_template_activeTab'), 2), activeTab = _a[0], setActiveTab = _a[1];
    var _b = __read((0, akeneo_design_system_1.useTabBar)(activeTab), 2), isCurrent = _b[0], switchTo = _b[1];
    var handleSwitchTo = function (tab) {
        setActiveTab(tab);
        switchTo(tab);
    };
    var tabInError = useTabInError();
    var _c = (0, react_router_1.useParams)(), treeId = _c.treeId, templateId = _c.templateId;
    var tree = (0, hooks_1.useCategoryTree)(treeId).data;
    var template = (0, hooks_1.useTemplateByTemplateUuid)(templateId).data;
    var catalogLocale = userContext.get('catalogLocale');
    var templateLabel = template && (0, shared_1.getLabel)(template.labels, catalogLocale, template.code);
    var _d = __read((0, akeneo_design_system_1.useBooleanState)(), 3), isDeactivateTemplateModelOpen = _d[0], openDeactivateTemplateModal = _d[1], closeDeactivateTemplateModal = _d[2];
    return ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsxs)(shared_1.PageHeader, { children: [(0, jsx_runtime_1.jsx)(shared_1.PageHeader.Breadcrumb, { children: (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Breadcrumb, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: function () { return router.redirect(router.generate('pim_settings_index')); } }, { children: translate('pim_menu.tab.settings') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: function () { return router.redirect(router.generate('pim_enrich_categorytree_index')); } }, { children: translate('pim_enrich.entity.category.plural_label') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: function () { return router.redirect(router.generate('pim_enrich_categorytree_tree', { id: treeId })); } }, { children: (tree === null || tree === void 0 ? void 0 : tree.label) || (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SkeletonPlaceholder, __assign({ as: "span" }, { children: treeId }), void 0) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, { children: templateLabel || (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SkeletonPlaceholder, __assign({ as: "span" }, { children: templateId }), void 0) }, void 0)] }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.AutoSaveStatus, { children: (0, jsx_runtime_1.jsx)(SaveStatusIndicator_1.SaveStatusIndicator, {}, void 0) }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.UserActions, { children: (0, jsx_runtime_1.jsx)(shared_1.PimView, { viewName: "pim-menu-user-navigation", className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu" }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.Actions, { children: (0, jsx_runtime_1.jsx)(TemplateOtherActions_1.TemplateOtherActions, { onDeactivateTemplate: openDeactivateTemplateModal }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.Title, { children: templateLabel }, void 0)] }, void 0), (0, jsx_runtime_1.jsxs)(PageContent, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.TabBar, __assign({ moreButtonTitle: 'More', sticky: 0 }, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.TabBar.Tab, __assign({ isActive: isCurrent(Tabs.ATTRIBUTE), onClick: function () { return handleSwitchTo(Tabs.ATTRIBUTE); } }, { children: [translate('akeneo.category.attributes'), " ", tabInError[Tabs.ATTRIBUTE] && (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Pill, { level: 'danger' }, void 0)] }), void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.TabBar.Tab, __assign({ isActive: isCurrent(Tabs.PROPERTY), onClick: function () { return handleSwitchTo(Tabs.PROPERTY); } }, { children: [translate('pim_common.properties'), " ", tabInError[Tabs.PROPERTY] && (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Pill, { level: 'danger' }, void 0)] }), void 0)] }), void 0), template && ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [isCurrent(Tabs.ATTRIBUTE) && ((0, jsx_runtime_1.jsx)(EditTemplateAttributesForm_1.EditTemplateAttributesForm, { attributes: template.attributes, templateId: template.uuid }, void 0)), isCurrent(Tabs.PROPERTY) && (0, jsx_runtime_1.jsx)(EditTemplatePropertiesForm_1.EditTemplatePropertiesForm, { template: template }, void 0), isDeactivateTemplateModelOpen && ((0, jsx_runtime_1.jsx)(DeactivateTemplateModal_1.DeactivateTemplateModal, { template: { id: template.uuid, label: templateLabel || '' }, onClose: closeDeactivateTemplateModal }, void 0))] }, void 0))] }, void 0)] }, void 0));
};
exports.TemplatePage = TemplatePage;
var PageContent = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  padding: 0 40px;\n  height: calc(100vh - 130px);\n"], ["\n  padding: 0 40px;\n  height: calc(100vh - 130px);\n"])));
var templateObject_1;
//# sourceMappingURL=TemplatePage.js.map
"use strict";
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
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
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
Object.defineProperty(exports, "__esModule", { value: true });
exports.CategoryEditPage = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var react_router_1 = require("react-router");
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var hooks_1 = require("../hooks");
var HistoryPimView_1 = require("./HistoryPimView");
var DeleteCategoryModal_1 = require("../components/datagrids/DeleteCategoryModal");
var components_1 = require("../components");
var templates_1 = require("../components/templates");
var Tabs;
(function (Tabs) {
    Tabs["ATTRIBUTE"] = "#pim_enrich-category-tab-attribute";
    Tabs["PROPERTY"] = "#pim_enrich-category-tab-property";
    Tabs["HISTORY"] = "#pim_enrich-category-tab-history";
    Tabs["PERMISSION"] = "#pim_enrich-category-tab-permission";
})(Tabs || (Tabs = {}));
var CategoryEditPage = function () {
    var categoryId = (0, react_router_1.useParams)().categoryId;
    var translate = (0, shared_1.useTranslate)();
    var router = (0, shared_1.useRouter)();
    var userContext = (0, shared_1.useUserContext)();
    var _a = __read((0, react_1.useState)(null), 2), catalogLocale = _a[0], setCatalogLocale = _a[1];
    var handleLocaleChanges = function (locale) {
        setCatalogLocale(locale);
        setLabels(locale);
    };
    var featureFlags = (0, shared_1.useFeatureFlags)();
    var permissionsAreEnabled = featureFlags.isEnabled('permission');
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var _b = (0, hooks_1.useDeleteCategory)(), isCategoryDeletionPossible = _b.isCategoryDeletionPossible, handleDeleteCategory = _b.handleDeleteCategory;
    var _c = __read((0, react_1.useState)(''), 2), categoryLabel = _c[0], setCategoryLabel = _c[1];
    var _d = __read((0, react_1.useState)(''), 2), treeLabel = _d[0], setTreeLabel = _d[1];
    var countProductsBeforeDeleteCategory = (0, hooks_1.useCountProductsBeforeDeleteCategory)(parseInt(categoryId));
    var _e = __read((0, react_1.useState)(null), 2), categoryToDelete = _e[0], setCategoryToDelete = _e[1];
    var _f = __read((0, react_1.useState)(null), 2), tree = _f[0], setTree = _f[1];
    var _g = (0, hooks_1.useEditCategoryForm)(parseInt(categoryId)), category = _g.category, template = _g.template, categoryFetchingStatus = _g.categoryFetchingStatus, applyPermissionsOnChildren = _g.applyPermissionsOnChildren, onChangeCategoryLabel = _g.onChangeCategoryLabel, onChangePermissions = _g.onChangePermissions, onChangeAttribute = _g.onChangeAttribute, onChangeApplyPermissionsOnChildren = _g.onChangeApplyPermissionsOnChildren, thereAreUnsavedChanges = _g.isModified, saveCategory = _g.saveCategory, historyVersion = _g.historyVersion;
    var _h = __read((0, shared_1.useSessionStorageState)(isGranted('pim_enrich_product_category_edit_attributes') ? Tabs.ATTRIBUTE : Tabs.PROPERTY, 'pim_category_activeTab'), 2), activeTab = _h[0], setActiveTab = _h[1];
    var _j = __read((0, akeneo_design_system_1.useTabBar)(activeTab), 2), isCurrent = _j[0], switchTo = _j[1];
    var _k = __read((0, akeneo_design_system_1.useBooleanState)(false), 3), secondaryActionIsOpen = _k[0], openSecondaryAction = _k[1], closeSecondaryAction = _k[2];
    var _l = __read((0, akeneo_design_system_1.useBooleanState)(), 3), isDeleteCategoryModalOpen = _l[0], openDeleteCategoryModal = _l[1], closeDeleteCategoryModal = _l[2];
    var handleSwitchTo = (0, react_1.useCallback)(function (tab) {
        setActiveTab(tab);
        switchTo(tab);
    }, [setActiveTab, switchTo]);
    (0, shared_1.useSetPageTitle)(translate('pim_title.pim_enrich_categorytree_edit', { 'category.label': categoryLabel }));
    var followSettingsIndex = function () { return router.redirect(router.generate('pim_settings_index')); };
    var followCategoriesIndex = function () { return router.redirect(router.generate('pim_enrich_categorytree_index')); };
    var followCategoryTree = function () {
        if (!tree) {
            return;
        }
        router.redirect(router.generate('pim_enrich_categorytree_tree', { id: tree.id }));
    };
    var handleCloseDeleteCategoryModal = function () {
        setCategoryToDelete(null);
        closeDeleteCategoryModal();
    };
    var onBuildHistoryView = (0, react_1.useCallback)(function (view) { return __awaiter(void 0, void 0, void 0, function () {
        return __generator(this, function (_a) {
            view.setData({ categoryId: categoryId });
            return [2, view];
        });
    }); }, [categoryId]);
    (0, react_1.useEffect)(function () {
        if (!category) {
            setCategoryLabel('');
            setTreeLabel('');
            setTree(null);
            return;
        }
        var catalogLocale = userContext.get('catalogLocale');
        setLabels(catalogLocale);
    }, [category, userContext]);
    var setLabels = function (locale) {
        if (category) {
            setCategoryLabel((0, shared_1.getLabel)(category.properties.labels, locale, category.properties.code));
            var root = category.root;
            if (category.isRoot) {
                root = category;
            }
            if (root) {
                var _a = root.properties, code = _a.code, labels = _a.labels;
                setTreeLabel((0, shared_1.getLabel)(labels, locale, code));
                setTree(root);
                sessionStorage.setItem('lastSelectedCategory', JSON.stringify({ treeId: root.id.toString(), categoryId: category.id }));
            }
        }
    };
    (0, react_1.useEffect)(function () {
        if (category === null)
            return;
        if (activeTab === Tabs.ATTRIBUTE &&
            (!isGranted('pim_enrich_product_category_edit_attributes') || !category.template_uuid)) {
            handleSwitchTo(Tabs.PROPERTY);
        }
    }, [category, activeTab]);
    if (categoryFetchingStatus === 'error') {
        return ((0, jsx_runtime_1.jsx)(shared_1.FullScreenError, { title: translate('error.exception', { status_code: '404' }), message: translate('pim_enrich.entity.category.content.edit.not_found'), code: 404 }, void 0));
    }
    var templateHasAttribute = function () {
        return (template === null || template === void 0 ? void 0 : template.attributes.length) !== 0;
    };
    return ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsxs)(shared_1.PageHeader, __assign({ showPlaceholder: categoryFetchingStatus === 'idle' || categoryFetchingStatus === 'fetching' }, { children: [(0, jsx_runtime_1.jsx)(shared_1.PageHeader.Breadcrumb, { children: (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Breadcrumb, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: followSettingsIndex }, { children: translate('pim_menu.tab.settings') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: followCategoriesIndex }, { children: translate('pim_enrich.entity.category.plural_label') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: followCategoryTree }, { children: treeLabel || (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SkeletonPlaceholder, __assign({ as: "span" }, { children: categoryId }), void 0) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, { children: categoryLabel || (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SkeletonPlaceholder, __assign({ as: "span" }, { children: categoryId }), void 0) }, void 0)] }, void 0) }, void 0), template && ((0, jsx_runtime_1.jsx)(shared_1.PageHeader.Content, { children: (0, jsx_runtime_1.jsx)(components_1.TemplateTitle, { template: template, locale: catalogLocale }, void 0) }, void 0)), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.UserActions, { children: (0, jsx_runtime_1.jsx)(shared_1.PimView, { viewName: "pim-menu-user-navigation", className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu" }, void 0) }, void 0), (0, jsx_runtime_1.jsxs)(shared_1.PageHeader.Actions, { children: [isGranted('pim_enrich_product_category_remove') && ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Dropdown, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.IconButton, { level: "tertiary", title: translate('pim_common.other_actions'), icon: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.MoreIcon, {}, void 0), ghost: "borderless", onClick: openSecondaryAction, className: "dropdown-button" }, void 0), secondaryActionIsOpen && ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Dropdown.Overlay, __assign({ onClose: closeSecondaryAction }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.Header, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.Title, { children: translate('pim_common.other_actions') }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.ItemCollection, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.Item, __assign({ onClick: function () {
                                                        countProductsBeforeDeleteCategory(function (nbProducts) {
                                                            var identifier = parseInt(categoryId);
                                                            if (category && isCategoryDeletionPossible(categoryLabel, nbProducts)) {
                                                                setCategoryToDelete({
                                                                    identifier: identifier,
                                                                    label: categoryLabel,
                                                                    code: category.properties.code,
                                                                    numberOfProducts: nbProducts,
                                                                    onDelete: followCategoryTree,
                                                                });
                                                                openDeleteCategoryModal();
                                                            }
                                                        });
                                                        closeSecondaryAction();
                                                    }, className: "AknDropdown-menuLink" }, { children: (0, jsx_runtime_1.jsx)("span", { children: translate('pim_common.delete') }, void 0) }), void 0) }, void 0)] }), void 0))] }, void 0)), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ level: "primary", onClick: saveCategory }, { children: translate('pim_common.save') }), void 0)] }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.Title, { children: categoryLabel !== null && categoryLabel !== void 0 ? categoryLabel : categoryId }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.State, { children: thereAreUnsavedChanges && (0, jsx_runtime_1.jsx)(shared_1.UnsavedChanges, {}, void 0) }, void 0)] }), void 0), (0, jsx_runtime_1.jsxs)(components_1.CategoryPageContent, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.TabBar, __assign({ moreButtonTitle: 'More', sticky: 0 }, { children: [isGranted('pim_enrich_product_category_edit_attributes') && template && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.TabBar.Tab, __assign({ isActive: isCurrent(Tabs.ATTRIBUTE), onClick: function () { return handleSwitchTo(Tabs.ATTRIBUTE); } }, { children: translate('akeneo.category.attributes') }), void 0)), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.TabBar.Tab, __assign({ isActive: isCurrent(Tabs.PROPERTY), onClick: function () { return handleSwitchTo(Tabs.PROPERTY); } }, { children: translate('pim_common.properties') }), void 0), category &&
                                category.permissions &&
                                permissionsAreEnabled &&
                                isGranted('pimee_enrich_category_edit_permissions') && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.TabBar.Tab, __assign({ isActive: isCurrent(Tabs.PERMISSION), onClick: function () { return handleSwitchTo(Tabs.PERMISSION); } }, { children: translate('pim_common.permissions') }), void 0)), isGranted('pim_enrich_product_category_history') && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.TabBar.Tab, __assign({ isActive: isCurrent(Tabs.HISTORY), onClick: function () { return handleSwitchTo(Tabs.HISTORY); } }, { children: translate('pim_common.history') }), void 0))] }), void 0), isGranted('pim_enrich_product_category_edit_attributes') &&
                        isCurrent(Tabs.ATTRIBUTE) &&
                        category &&
                        template &&
                        !templateHasAttribute() && (0, jsx_runtime_1.jsx)(templates_1.NoTemplateAttribute, {}, void 0), isGranted('pim_enrich_product_category_edit_attributes') &&
                        isCurrent(Tabs.ATTRIBUTE) &&
                        category &&
                        template &&
                        templateHasAttribute() && ((0, jsx_runtime_1.jsx)(components_1.EditAttributesForm, { attributeValues: category.attributes, template: template, onAttributeValueChange: onChangeAttribute, onLocaleChange: handleLocaleChanges }, void 0)), isCurrent(Tabs.PROPERTY) && category && ((0, jsx_runtime_1.jsx)(components_1.EditPropertiesForm, { category: category, onChangeLabel: onChangeCategoryLabel }, void 0)), isCurrent(Tabs.PERMISSION) && category && permissionsAreEnabled && ((0, jsx_runtime_1.jsx)(components_1.EditPermissionsForm, { category: category, applyPermissionsOnChildren: applyPermissionsOnChildren, onChangePermissions: onChangePermissions, onChangeApplyPermissionsOnChildren: onChangeApplyPermissionsOnChildren }, void 0)), isCurrent(Tabs.HISTORY) && ((0, jsx_runtime_1.jsx)(HistoryPimView_1.HistoryPimView, { viewName: "pim-category-edit-form-history", onBuild: onBuildHistoryView, version: historyVersion }, void 0))] }, void 0), isDeleteCategoryModalOpen && categoryToDelete !== null && ((0, jsx_runtime_1.jsx)(DeleteCategoryModal_1.DeleteCategoryModal, { categoryLabel: categoryToDelete.label, closeModal: handleCloseDeleteCategoryModal, deleteCategory: function () { return __awaiter(void 0, void 0, void 0, function () {
                    return __generator(this, function (_a) {
                        switch (_a.label) {
                            case 0: return [4, handleDeleteCategory(categoryToDelete)];
                            case 1:
                                _a.sent();
                                handleCloseDeleteCategoryModal();
                                return [2];
                        }
                    });
                }); }, message: 'pim_enrich.entity.category.category_deletion.confirmation_question', categoryId: categoryToDelete.identifier, numberOfProducts: categoryToDelete.numberOfProducts }, void 0))] }, void 0));
};
exports.CategoryEditPage = CategoryEditPage;
//# sourceMappingURL=CategoryEditPage.js.map
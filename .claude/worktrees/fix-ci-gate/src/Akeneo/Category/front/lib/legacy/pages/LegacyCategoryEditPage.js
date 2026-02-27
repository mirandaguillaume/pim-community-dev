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
exports.LegacyCategoryEditPage = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var react_router_1 = require("react-router");
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var hooks_1 = require("../hooks");
var pages_1 = require("../../pages");
var DeleteCategoryModal_1 = require("../components/datagrids/DeleteCategoryModal");
var components_1 = require("../components");
var propertyTabName = '#pim_enrich-category-tab-property';
var historyTabName = '#pim_enrich-category-tab-history';
var permissionTabName = '#pim_enrich-category-tab-permission';
var LegacyCategoryEditPage = function () {
    var categoryId = (0, react_router_1.useParams)().categoryId;
    var translate = (0, shared_1.useTranslate)();
    var router = (0, shared_1.useRouter)();
    var userContext = (0, shared_1.useUserContext)();
    var featureFlags = (0, shared_1.useFeatureFlags)();
    var permissionsAreEnabled = featureFlags.isEnabled('permission');
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var _a = (0, hooks_1.useDeleteCategory)(), isCategoryDeletionPossible = _a.isCategoryDeletionPossible, handleDeleteCategory = _a.handleDeleteCategory;
    var _b = __read((0, react_1.useState)(''), 2), categoryLabel = _b[0], setCategoryLabel = _b[1];
    var _c = __read((0, react_1.useState)(''), 2), treeLabel = _c[0], setTreeLabel = _c[1];
    var countProductsBeforeDeleteCategory = (0, hooks_1.useCountProductsBeforeDeleteCategory)(parseInt(categoryId));
    var _d = __read((0, react_1.useState)(null), 2), categoryToDelete = _d[0], setCategoryToDelete = _d[1];
    var _e = __read((0, react_1.useState)(null), 2), tree = _e[0], setTree = _e[1];
    var _f = __read((0, shared_1.useSessionStorageState)(propertyTabName, 'pim_category_activeTab'), 2), activeTab = _f[0], setActiveTab = _f[1];
    var _g = __read((0, akeneo_design_system_1.useTabBar)(activeTab), 2), isCurrent = _g[0], switchTo = _g[1];
    var _h = __read((0, akeneo_design_system_1.useBooleanState)(false), 3), secondaryActionIsOpen = _h[0], openSecondaryAction = _h[1], closeSecondaryAction = _h[2];
    var _j = __read((0, akeneo_design_system_1.useBooleanState)(), 3), isDeleteCategoryModalOpen = _j[0], openDeleteCategoryModal = _j[1], closeDeleteCategoryModal = _j[2];
    var _k = (0, hooks_1.useEditCategoryForm)(parseInt(categoryId)), category = _k.category, categoryLoadingStatus = _k.categoryLoadingStatus, formData = _k.formData, onChangeCategoryLabel = _k.onChangeCategoryLabel, onChangePermissions = _k.onChangePermissions, onChangeApplyPermissionsOnChildren = _k.onChangeApplyPermissionsOnChildren, thereAreUnsavedChanges = _k.thereAreUnsavedChanges, saveCategory = _k.saveCategory, historyVersion = _k.historyVersion;
    (0, shared_1.useSetPageTitle)(translate('pim_title.pim_enrich_categorytree_edit', { 'category.label': categoryLabel }));
    var uiLocale = userContext.get('uiLocale');
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
        var rootCategory = category.root ? category.root : category;
        setCategoryLabel((0, shared_1.getLabel)(category.labels, catalogLocale, category.code));
        setTreeLabel((0, shared_1.getLabel)(rootCategory.labels, catalogLocale, rootCategory.code));
        setTree(rootCategory);
        sessionStorage.setItem('lastSelectedCategory', JSON.stringify({ treeId: rootCategory.id.toString(), categoryId: category.id }));
    }, [category, userContext]);
    if (categoryLoadingStatus === 'error') {
        return ((0, jsx_runtime_1.jsx)(shared_1.FullScreenError, { title: translate('error.exception', { status_code: '404' }), message: translate('pim_enrich.entity.category.content.edit.not_found'), code: 404 }, void 0));
    }
    return ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsxs)(shared_1.PageHeader, __assign({ showPlaceholder: categoryLoadingStatus === 'idle' || categoryLoadingStatus === 'fetching' }, { children: [(0, jsx_runtime_1.jsx)(shared_1.PageHeader.Breadcrumb, { children: (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Breadcrumb, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: followSettingsIndex }, { children: translate('pim_menu.tab.settings') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: followCategoriesIndex }, { children: translate('pim_enrich.entity.category.plural_label') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: followCategoryTree }, { children: treeLabel || (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SkeletonPlaceholder, __assign({ as: "span" }, { children: categoryId }), void 0) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, { children: categoryLabel || (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SkeletonPlaceholder, __assign({ as: "span" }, { children: categoryId }), void 0) }, void 0)] }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.UserActions, { children: (0, jsx_runtime_1.jsx)(shared_1.PimView, { viewName: "pim-menu-user-navigation", className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu" }, void 0) }, void 0), (0, jsx_runtime_1.jsxs)(shared_1.PageHeader.Actions, { children: [isGranted('pim_enrich_product_category_remove') && ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Dropdown, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.IconButton, { level: "tertiary", title: translate('pim_common.other_actions'), icon: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.MoreIcon, {}, void 0), ghost: "borderless", onClick: openSecondaryAction, className: "dropdown-button" }, void 0), secondaryActionIsOpen && ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Dropdown.Overlay, __assign({ onClose: closeSecondaryAction }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.Header, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.Title, { children: translate('pim_common.other_actions') }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.ItemCollection, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Dropdown.Item, __assign({ onClick: function () {
                                                        countProductsBeforeDeleteCategory(function (nbProducts) {
                                                            var identifier = parseInt(categoryId);
                                                            if (category && isCategoryDeletionPossible(category.labels[uiLocale], nbProducts)) {
                                                                setCategoryToDelete({
                                                                    identifier: identifier,
                                                                    label: category.labels[uiLocale],
                                                                    onDelete: followCategoryTree,
                                                                });
                                                                openDeleteCategoryModal();
                                                            }
                                                        });
                                                        closeSecondaryAction();
                                                    }, className: "AknDropdown-menuLink" }, { children: (0, jsx_runtime_1.jsx)("span", { children: translate('pim_common.delete') }, void 0) }), void 0) }, void 0)] }), void 0))] }, void 0)), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ level: "primary", onClick: saveCategory }, { children: translate('pim_common.save') }), void 0)] }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.Title, { children: categoryLabel !== null && categoryLabel !== void 0 ? categoryLabel : categoryId }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.State, { children: thereAreUnsavedChanges && (0, jsx_runtime_1.jsx)(shared_1.UnsavedChanges, {}, void 0) }, void 0)] }), void 0), (0, jsx_runtime_1.jsxs)(shared_1.PageContent, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.TabBar, __assign({ moreButtonTitle: 'More' }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.TabBar.Tab, __assign({ isActive: isCurrent(propertyTabName), onClick: function () {
                                    setActiveTab(propertyTabName);
                                    switchTo(propertyTabName);
                                } }, { children: translate('pim_common.properties') }), void 0), formData &&
                                formData.permissions &&
                                permissionsAreEnabled &&
                                isGranted('pimee_enrich_category_edit_permissions') && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.TabBar.Tab, __assign({ isActive: isCurrent(permissionTabName), onClick: function () {
                                    setActiveTab(permissionTabName);
                                    switchTo(permissionTabName);
                                } }, { children: translate('pim_common.permissions') }), void 0)), isGranted('pim_enrich_product_category_history') && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.TabBar.Tab, __assign({ isActive: isCurrent(historyTabName), onClick: function () {
                                    setActiveTab(historyTabName);
                                    switchTo(historyTabName);
                                } }, { children: translate('pim_common.history') }), void 0))] }), void 0), isCurrent(propertyTabName) && category && ((0, jsx_runtime_1.jsx)(components_1.EditPropertiesForm, { category: category, formData: formData, onChangeLabel: onChangeCategoryLabel }, void 0)), isCurrent(historyTabName) && ((0, jsx_runtime_1.jsx)(pages_1.HistoryPimView, { viewName: "pim-category-edit-form-history", onBuild: onBuildHistoryView, version: historyVersion }, void 0)), isCurrent(permissionTabName) && permissionsAreEnabled && ((0, jsx_runtime_1.jsx)(components_1.EditPermissionsForm, { formData: formData, onChangePermissions: onChangePermissions, onChangeApplyPermissionsOnChildren: onChangeApplyPermissionsOnChildren }, void 0))] }, void 0), isDeleteCategoryModalOpen && categoryToDelete !== null && ((0, jsx_runtime_1.jsx)(DeleteCategoryModal_1.DeleteCategoryModal, { categoryLabel: categoryToDelete.label, closeModal: handleCloseDeleteCategoryModal, deleteCategory: function () { return __awaiter(void 0, void 0, void 0, function () {
                    return __generator(this, function (_a) {
                        switch (_a.label) {
                            case 0: return [4, handleDeleteCategory(categoryToDelete)];
                            case 1:
                                _a.sent();
                                handleCloseDeleteCategoryModal();
                                return [2];
                        }
                    });
                }); }, message: 'pim_enrich.entity.category.category_deletion.confirmation' }, void 0))] }, void 0));
};
exports.LegacyCategoryEditPage = LegacyCategoryEditPage;
//# sourceMappingURL=LegacyCategoryEditPage.js.map
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
exports.CategoriesIndex = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var components_1 = require("../components");
var hooks_1 = require("../hooks");
var NewCategoryModal_1 = require("./NewCategoryModal");
var CategoriesIndex = function () {
    var router = (0, shared_1.useRouter)();
    var translate = (0, shared_1.useTranslate)();
    var _a = (0, hooks_1.useCategoryTreeList)(), trees = _a.trees, loadingStatus = _a.loadingStatus, loadTrees = _a.loadTrees;
    var _b = __read((0, akeneo_design_system_1.useBooleanState)(), 3), isModalOpen = _b[0], openModal = _b[1], closeModal = _b[2];
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var followSettingsIndex = function () { return router.redirect(router.generate('pim_settings_index')); };
    (0, react_1.useEffect)(function () {
        loadTrees();
        sessionStorage.removeItem('lastSelectedCategory');
    }, [loadTrees]);
    return ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsxs)(shared_1.PageHeader, __assign({ showPlaceholder: loadingStatus === 'idle' || loadingStatus === 'fetching' }, { children: [(0, jsx_runtime_1.jsx)(shared_1.PageHeader.Breadcrumb, { children: (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Breadcrumb, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: followSettingsIndex }, { children: translate('pim_menu.tab.settings') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, { children: translate('pim_enrich.entity.category.plural_label') }, void 0)] }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.UserActions, { children: (0, jsx_runtime_1.jsx)(shared_1.PimView, { viewName: "pim-menu-user-navigation", className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu" }, void 0) }, void 0), isGranted('pim_enrich_product_category_create') && ((0, jsx_runtime_1.jsx)(shared_1.PageHeader.Actions, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ onClick: openModal, level: "primary" }, { children: translate('akeneo.category.tree.create') }), void 0) }, void 0)), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.Title, { children: translate('pim_enrich.entity.category.page_title.index', { count: trees.length }, trees.length) }, void 0)] }), void 0), (0, jsx_runtime_1.jsx)(shared_1.PageContent, { children: trees.length === 0 ? ((0, jsx_runtime_1.jsx)(components_1.EmptyCategoryTreeList, {}, void 0)) : ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsx)(components_1.DiscoverEnrichedCategoriesInformationHelper, {}, void 0), (0, jsx_runtime_1.jsx)(components_1.CategoryTreesDataGrid, { trees: trees, refreshCategoryTrees: loadTrees }, void 0)] }, void 0)) }, void 0), isModalOpen && (0, jsx_runtime_1.jsx)(NewCategoryModal_1.NewCategoryModal, { closeModal: closeModal, onCreate: loadTrees }, void 0)] }, void 0));
};
exports.CategoriesIndex = CategoriesIndex;
//# sourceMappingURL=CategoriesIndex.js.map
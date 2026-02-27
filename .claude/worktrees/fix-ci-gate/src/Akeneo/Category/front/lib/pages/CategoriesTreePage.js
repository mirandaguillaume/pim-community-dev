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
exports.CategoriesTreePage = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var react_router_1 = require("react-router");
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var hooks_1 = require("../hooks");
var components_1 = require("../components");
var NewCategoryModal_1 = require("./NewCategoryModal");
var DeleteCategoryModal_1 = require("../components/datagrids/DeleteCategoryModal");
var CategoriesTreePage = function () {
    var _a;
    var treeId = (0, react_router_1.useParams)().treeId;
    var router = (0, shared_1.useRouter)();
    var translate = (0, shared_1.useTranslate)();
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var _b = __read((0, shared_1.useSessionStorageState)({
        treeId: treeId,
        categoryId: '-1',
    }, 'lastSelectedCategory'), 1), lastSelectedCategory = _b[0];
    var _c = (0, hooks_1.useCategoryTreeDeprecated)(parseInt(treeId), lastSelectedCategory.treeId === treeId ? lastSelectedCategory.categoryId : '-1'), tree = _c.tree, loadingStatus = _c.loadingStatus, loadTree = _c.loadTree;
    var _d = __read((0, react_1.useState)(''), 2), treeLabel = _d[0], setTreeLabel = _d[1];
    var _e = __read((0, akeneo_design_system_1.useBooleanState)(), 3), isNewCategoryModalOpen = _e[0], openNewCategoryModal = _e[1], closeNewCategoryModal = _e[2];
    var _f = __read((0, react_1.useState)(null), 2), categoryToCreate = _f[0], setCategoryToCreate = _f[1];
    var _g = __read((0, akeneo_design_system_1.useBooleanState)(), 3), isDeleteCategoryModalOpen = _g[0], openDeleteCategoryModal = _g[1], closeDeleteCategoryModal = _g[2];
    var _h = __read((0, react_1.useState)(null), 2), categoryToDelete = _h[0], setCategoryToDelete = _h[1];
    var _j = (0, hooks_1.useDeleteCategory)(), isCategoryDeletionPossible = _j.isCategoryDeletionPossible, handleDeleteCategory = _j.handleDeleteCategory;
    (0, shared_1.useSetPageTitle)(translate('pim_title.pim_enrich_categorytree_tree', { 'category.label': treeLabel }));
    var followSettingsIndex = function () { return router.redirect(router.generate('pim_settings_index')); };
    var followCategoriesIndex = function () { return router.redirect(router.generate('pim_enrich_categorytree_index')); };
    var followEditCategory = function (id) {
        if (!isGranted('pim_enrich_product_category_edit')) {
            return;
        }
        router.redirect(router.generate('pim_enrich_categorytree_edit', { id: id.toString() }));
    };
    var addCategory = function (parentCode, onCreate) {
        setCategoryToCreate({ parentCode: parentCode, onCreate: onCreate });
        openNewCategoryModal();
    };
    var handleCloseNewCategoryModal = function () {
        setCategoryToCreate(null);
        closeNewCategoryModal();
    };
    var confirmDeleteCategory = function (identifier, label, code, numberOfProducts, onDelete) { return __awaiter(void 0, void 0, void 0, function () {
        return __generator(this, function (_a) {
            if (isCategoryDeletionPossible(label, numberOfProducts)) {
                setCategoryToDelete({ identifier: identifier, label: label, code: code, numberOfProducts: numberOfProducts, onDelete: onDelete });
                openDeleteCategoryModal();
            }
            return [2];
        });
    }); };
    var handleCloseDeleteCategoryModal = function () {
        setCategoryToDelete(null);
        closeDeleteCategoryModal();
    };
    (0, react_1.useEffect)(function () {
        loadTree();
    }, [loadTree, treeId]);
    (0, react_1.useEffect)(function () {
        setTreeLabel(tree ? tree.label : '');
    }, [tree]);
    if (loadingStatus === 'error') {
        return ((0, jsx_runtime_1.jsx)(shared_1.FullScreenError, { title: translate('error.exception', { status_code: '404' }), message: translate('pim_enrich.entity.category.content.tree.not_found'), code: 404 }, void 0));
    }
    return ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsxs)(shared_1.PageHeader, __assign({ showPlaceholder: loadingStatus === 'idle' || loadingStatus === 'fetching' }, { children: [(0, jsx_runtime_1.jsx)(shared_1.PageHeader.Breadcrumb, { children: (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Breadcrumb, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: followSettingsIndex }, { children: translate('pim_menu.tab.settings') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, __assign({ onClick: followCategoriesIndex }, { children: translate('pim_enrich.entity.category.plural_label') }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Breadcrumb.Step, { children: treeLabel || (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SkeletonPlaceholder, __assign({ as: "span" }, { children: treeId }), void 0) }, void 0)] }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.UserActions, { children: (0, jsx_runtime_1.jsx)(shared_1.PimView, { viewName: "pim-menu-user-navigation", className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu" }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageHeader.Title, { children: (_a = tree === null || tree === void 0 ? void 0 : tree.label) !== null && _a !== void 0 ? _a : treeId }, void 0)] }), void 0), (0, jsx_runtime_1.jsxs)(shared_1.PageContent, { children: [(0, jsx_runtime_1.jsxs)("section", { children: [(0, jsx_runtime_1.jsx)(components_1.DiscoverEnrichedCategoriesInformationHelper, {}, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, { children: translate('pim_enrich.entity.category.plural_label') }, void 0) }, void 0), (0, jsx_runtime_1.jsx)(components_1.CategoryTree, { root: tree, orderable: isGranted('pim_enrich_product_category_order_trees'), followCategory: isGranted('pim_enrich_product_category_edit') ? function (cat) { return followEditCategory(cat.id); } : undefined, addCategory: isGranted('pim_enrich_product_category_create') ? addCategory : undefined, deleteCategory: isGranted('pim_enrich_product_category_remove') ? confirmDeleteCategory : undefined }, void 0)] }, void 0), isNewCategoryModalOpen && categoryToCreate !== null && ((0, jsx_runtime_1.jsx)(NewCategoryModal_1.NewCategoryModal, { closeModal: handleCloseNewCategoryModal, onCreate: categoryToCreate.onCreate, parentCode: categoryToCreate.parentCode }, void 0)), isDeleteCategoryModalOpen && categoryToDelete !== null && ((0, jsx_runtime_1.jsx)(DeleteCategoryModal_1.DeleteCategoryModal, { categoryLabel: categoryToDelete.label, closeModal: handleCloseDeleteCategoryModal, deleteCategory: function () { return __awaiter(void 0, void 0, void 0, function () {
                            return __generator(this, function (_a) {
                                switch (_a.label) {
                                    case 0: return [4, handleDeleteCategory(categoryToDelete)];
                                    case 1:
                                        _a.sent();
                                        handleCloseDeleteCategoryModal();
                                        return [2];
                                }
                            });
                        }); }, message: 'pim_enrich.entity.category.category_deletion.confirmation_question', categoryId: categoryToDelete.identifier, numberOfProducts: categoryToDelete.numberOfProducts }, void 0))] }, void 0)] }, void 0));
};
exports.CategoriesTreePage = CategoriesTreePage;
//# sourceMappingURL=CategoriesTreePage.js.map
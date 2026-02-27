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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.CategoryTreesDataGrid = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var styled_components_1 = __importDefault(require("styled-components"));
var NoResults_1 = require("./NoResults");
var DeleteCategoryModal_1 = require("./DeleteCategoryModal");
var infrastructure_1 = require("../../infrastructure");
var hooks_1 = require("../../hooks");
var CreateTemplateModal_1 = require("../templates/CreateTemplateModal");
var CategoryTreesDataGrid = function (_a) {
    var trees = _a.trees, refreshCategoryTrees = _a.refreshCategoryTrees;
    var translate = (0, shared_1.useTranslate)();
    var router = (0, shared_1.useRouter)();
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var _b = __read((0, react_1.useState)(''), 2), searchString = _b[0], setSearchString = _b[1];
    var _c = __read((0, react_1.useState)(trees), 2), filteredTrees = _c[0], setFilteredTrees = _c[1];
    var notify = (0, shared_1.useNotify)();
    var _d = __read((0, akeneo_design_system_1.useBooleanState)(), 3), isConfirmationModalOpen = _d[0], openConfirmationModal = _d[1], closeConfirmationModal = _d[2];
    var _e = __read((0, react_1.useState)(null), 2), categoryTreeToDelete = _e[0], setCategoryTreeToDelete = _e[1];
    var _f = __read((0, react_1.useState)(null), 2), categoryTreeForTemplateCreation = _f[0], setCategoryTreeForTemplateCreation = _f[1];
    var _g = __read((0, react_1.useState)(false), 2), displayCategoryTemplatesColumn = _g[0], setDisplayCategoryTemplatesColumn = _g[1];
    var followCategoryTree = (0, react_1.useCallback)(function (tree) {
        var url = router.generate('pim_enrich_categorytree_tree', { id: tree.id });
        router.redirect(url);
        return;
    }, [router]);
    var search = (0, react_1.useCallback)(function (searchString) {
        setFilteredTrees(trees.filter(function (tree) {
            return (tree.code.toLocaleLowerCase().includes(searchString.toLowerCase().trim()) ||
                tree.label.toLocaleLowerCase().includes(searchString.toLowerCase().trim()));
        }));
    }, [trees]);
    var deleteCategoryTree = function () { return __awaiter(void 0, void 0, void 0, function () {
        var response, message;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    if (!categoryTreeToDelete) return [3, 2];
                    return [4, (0, infrastructure_1.deleteCategory)(router, categoryTreeToDelete.id)];
                case 1:
                    response = _a.sent();
                    response.ok && refreshCategoryTrees();
                    message = response.ok
                        ? 'pim_enrich.entity.category.category_tree_deletion.success'
                        : response.errorMessage || 'pim_enrich.entity.category.category_tree_deletion.error';
                    notify(response.ok ? shared_1.NotificationLevel.SUCCESS : shared_1.NotificationLevel.ERROR, translate(message, { tree: categoryTreeToDelete.label }));
                    setCategoryTreeToDelete(null);
                    _a.label = 2;
                case 2:
                    closeConfirmationModal();
                    return [2];
            }
        });
    }); };
    var _h = __read((0, akeneo_design_system_1.useBooleanState)(false), 3), isCreateTemplateModalOpen = _h[0], openCreateTemplateModal = _h[1], closeCreateTemplateModal = _h[2];
    var handleCreateTemplate = function (categoryTree) {
        setCategoryTreeForTemplateCreation(categoryTree);
        openCreateTemplateModal();
    };
    var redirectToTemplate = function (treeId, templateUuid) {
        router.redirect(router.generate('pim_category_template_edit', {
            treeId: treeId,
            templateUuid: templateUuid,
        }));
    };
    var onDeleteCategoryTree = function (categoryTree) {
        if (categoryTree.productsNumber && categoryTree.productsNumber > 100) {
            notify(shared_1.NotificationLevel.ERROR, translate('pim_enrich.entity.category.category_tree_deletion.products_limit_exceeded.title'), translate('pim_enrich.entity.category.category_tree_deletion.products_limit_exceeded.message', {
                tree: categoryTree.label,
                limit: 100,
            }));
            return;
        }
        setCategoryTreeToDelete(categoryTree);
        openConfirmationModal();
    };
    (0, react_1.useEffect)(function () {
        setFilteredTrees(trees);
        setSearchString('');
    }, [trees]);
    var debouncedSearch = (0, shared_1.useDebounceCallback)(search, 300);
    var onSearch = function (searchValue) {
        setSearchString(searchValue);
        debouncedSearch(searchValue);
    };
    var countTreesChildren = (0, hooks_1.useCountCategoryTreesChildren)();
    var displayTemplateLabelOrCode = function (categoryTree) {
        if (categoryTree.templateLabel) {
            return categoryTree.templateLabel;
        }
        if (categoryTree.templateCode) {
            return '[' + categoryTree.templateCode + ']';
        }
        return '';
    };
    (0, react_1.useEffect)(function () {
        var hasRights = isGranted('pim_enrich_product_category_template') || isGranted('pim_enrich_product_category_edit_attributes');
        var hasTemplates = false;
        filteredTrees.map(function (tree) {
            if (tree.templateCode) {
                hasTemplates = true;
            }
            return hasTemplates;
        });
        setDisplayCategoryTemplatesColumn(hasRights && hasTemplates);
    }, [filteredTrees, isGranted]);
    return ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsx)(StyledSearch, __assign({ sticky: 0, searchValue: searchString, onSearchChange: onSearch, placeholder: translate('pim_common.search') }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Search.ResultCount, { children: translate('pim_common.result_count', { itemsCount: filteredTrees.length.toString() }, filteredTrees.length) }, void 0) }), void 0), filteredTrees.length === 0 && searchString !== '' && ((0, jsx_runtime_1.jsx)(NoResults_1.NoResults, { title: translate('pim_datagrid.no_results', {
                    entityHint: translate('pim_enrich.entity.category.label'),
                }), subtitle: translate('pim_datagrid.no_results_subtitle') }, void 0)), filteredTrees.length > 0 && ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Table, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Table.Header, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.HeaderCell, { children: translate('pim_enrich.entity.category.content.tree_list.columns.label') }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.HeaderCell, { children: translate('pim_enrich.entity.category.content.tree_list.columns.number_of_categories') }, void 0), displayCategoryTemplatesColumn && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.HeaderCell, { children: translate('akeneo.category.tree_list.column.category_templates') }, void 0)), (0, jsx_runtime_1.jsx)(StyleActionHeader, { children: translate('pim_enrich.entity.category.content.tree_list.columns.actions') }, void 0)] }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.Body, { children: filteredTrees.map(function (tree) { return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Table.Row, __assign({ onClick: isGranted('pim_enrich_product_category_list') ? function () { return followCategoryTree(tree); } : undefined }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.Cell, __assign({ rowTitle: true }, { children: tree.label }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.Cell, { children: countTreesChildren !== null &&
                                                translate('pim_enrich.entity.category.content.tree_list.columns.count_categories', { count: countTreesChildren.hasOwnProperty(tree.code) ? countTreesChildren[tree.code] : 0 }, countTreesChildren.hasOwnProperty(tree.code) ? countTreesChildren[tree.code] : 0) }, void 0), displayCategoryTemplatesColumn && (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.Cell, { children: displayTemplateLabelOrCode(tree) }, void 0), (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Table.ActionCell, { children: [isGranted('pim_enrich_product_category_template') && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ ghost: true, level: "tertiary", size: 'small', onClick: function () {
                                                        tree.templateUuid
                                                            ? redirectToTemplate(tree.id, tree.templateUuid)
                                                            : handleCreateTemplate(tree);
                                                    }, disabled: !tree.hasOwnProperty('productsNumber') }, { children: translate(tree.templateUuid ? 'akeneo.category.template.edit' : 'akeneo.category.template.create') }), void 0)), isGranted('pim_enrich_product_category_remove') && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ ghost: true, level: "danger", size: 'small', onClick: function () { return onDeleteCategoryTree(tree); }, disabled: !tree.hasOwnProperty('productsNumber') }, { children: translate('akeneo.category.tree.delete') }), void 0))] }, void 0)] }), tree.code)); }) }, void 0)] }, void 0), isCreateTemplateModalOpen && categoryTreeForTemplateCreation && ((0, jsx_runtime_1.jsx)(CreateTemplateModal_1.CreateTemplateModal, { categoryTree: categoryTreeForTemplateCreation, onClose: function () {
                            refreshCategoryTrees();
                            closeCreateTemplateModal();
                        } }, void 0)), isConfirmationModalOpen && categoryTreeToDelete && ((0, jsx_runtime_1.jsx)(DeleteCategoryModal_1.DeleteCategoryModal, { categoryLabel: categoryTreeToDelete.label, closeModal: closeConfirmationModal, deleteCategory: deleteCategoryTree, message: 'pim_enrich.entity.category.category_tree_deletion.confirmation_question', categoryId: categoryTreeToDelete.id, numberOfProducts: categoryTreeToDelete.productsNumber }, void 0))] }, void 0))] }, void 0));
};
exports.CategoryTreesDataGrid = CategoryTreesDataGrid;
var StyledSearch = (0, styled_components_1.default)(akeneo_design_system_1.Search)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-bottom: 20px;\n"], ["\n  margin-bottom: 20px;\n"])));
var StyleActionHeader = (0, styled_components_1.default)(akeneo_design_system_1.Table.HeaderCell)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  width: 400px;\n"], ["\n  width: 400px;\n"])));
var templateObject_1, templateObject_2;
//# sourceMappingURL=CategoryTreeDataGrid.js.map
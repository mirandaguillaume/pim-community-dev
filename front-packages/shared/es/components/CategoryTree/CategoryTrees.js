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
import React from 'react';
import { CategoryTree } from './CategoryTree';
import { BooleanInput } from 'akeneo-design-system/lib/components/Input/BooleanInput/BooleanInput';
import { Tree, getColor } from 'akeneo-design-system';
import { useTranslate } from '../../hooks';
import { CategoryTreeSwitcher } from './CategoryTreeSwitcher';
import styled from 'styled-components';
var CategoryTreesContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  height: calc(100vh - 110px);\n  border-bottom: 1px solid ", ";\n  margin-bottom: 10px;\n"], ["\n  height: calc(100vh - 110px);\n  border-bottom: 1px solid ", ";\n  margin-bottom: 10px;\n"])), getColor('grey', 80));
var CategoryTreeContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  max-height: calc(100vh - 223px);\n  overflow: hidden auto;\n"], ["\n  max-height: calc(100vh - 223px);\n  overflow: hidden auto;\n"])));
var CategoryTrees = function (_a) {
    var init = _a.init, initTree = _a.initTree, childrenCallback = _a.childrenCallback, onTreeChange = _a.onTreeChange, onCategoryClick = _a.onCategoryClick, initialIncludeSubCategories = _a.initialIncludeSubCategories, onIncludeSubCategoriesChange = _a.onIncludeSubCategoriesChange, initialSelectedNodeId = _a.initialSelectedNodeId, initCallback = _a.initCallback;
    var translate = useTranslate();
    var _b = React.useState(), trees = _b[0], setTrees = _b[1];
    var _c = React.useState(initialIncludeSubCategories), includeSubCategories = _c[0], setIncludeSubCategories = _c[1];
    var _d = React.useState(initialSelectedNodeId), selectedNodeId = _d[0], setSelectedNodeId = _d[1];
    var customInitTree = React.useMemo(function () { return function (tree) {
        return initTree(tree.id, tree.label, tree.code, includeSubCategories);
    }; }, [includeSubCategories]);
    React.useEffect(function () {
        setTrees(undefined);
        init().then(function (categoryTreeRoots) { return setTrees(categoryTreeRoots); });
    }, [includeSubCategories]);
    if (!trees) {
        return React.createElement(Tree, { isLoading: true, label: "", value: "" });
    }
    var switchTree = function (treeId) {
        setTrees(trees.map(function (tree) {
            return __assign(__assign({}, tree), { selected: treeId === tree.id });
        }));
        setSelectedNodeId(function (previousSelectedNodeId) {
            return previousSelectedNodeId > 0 ? treeId : previousSelectedNodeId;
        });
        onTreeChange(treeId, (trees.find(function (tree) { return tree.id === treeId; }) || trees[0]).label, selectedNodeId);
    };
    var handleClick = function (category) {
        setSelectedNodeId(category.id);
        var selectedTree = trees.find(function (tree) { return tree.selected; }) || trees[0];
        onCategoryClick(category.id, selectedTree.id, category.id === selectedTree.id ? '' : category.label, selectedTree.label);
    };
    var handleIncludeSubCategoriesChange = function (value) {
        onIncludeSubCategoriesChange(value);
        setIncludeSubCategories(value);
    };
    var handleInitCallback = function (treeLabel, categoryLabel) {
        if (!initCallback) {
            return undefined;
        }
        return initCallback(treeLabel, categoryLabel ? categoryLabel : translate('jstree.all'));
    };
    var AllProductsTree = (React.createElement(Tree, { value: { id: -2, code: 'all_products' }, label: translate('jstree.all'), isLeaf: true, onClick: function () { return handleClick({ id: -2, code: 'all_products', label: translate('jstree.all') }); }, selected: selectedNodeId === -2 }));
    var isCategorySelected = function (category) {
        return category.id === selectedNodeId;
    };
    return (React.createElement(React.Fragment, null,
        React.createElement(CategoryTreesContainer, null,
            React.createElement(CategoryTreeSwitcher, { trees: trees, onClick: switchTree }),
            React.createElement(CategoryTreeContainer, null,
                trees.map(function (tree) {
                    return (tree.selected && (React.createElement(CategoryTree, { key: tree.code, categoryTreeCode: tree.code, init: function () { return customInitTree(tree); }, childrenCallback: childrenCallback, onClick: handleClick, isCategorySelected: isCategorySelected, initCallback: handleInitCallback })));
                }),
                AllProductsTree)),
        React.createElement("label", null, translate('jstree.include_sub')),
        React.createElement(BooleanInput, { value: includeSubCategories, readOnly: false, yesLabel: translate('pim_common.yes'), noLabel: translate('pim_common.no'), onChange: handleIncludeSubCategoriesChange })));
};
export { CategoryTrees };
var templateObject_1, templateObject_2;
//# sourceMappingURL=CategoryTrees.js.map
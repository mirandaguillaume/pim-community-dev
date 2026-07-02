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
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
import React from 'react';
import { RecursiveCategoryTree } from './RecursiveCategoryTree';
import { Tree } from 'akeneo-design-system/lib/components/Tree/Tree';
var CategoryTree = function (_a) {
    var categoryTreeCode = _a.categoryTreeCode, init = _a.init, childrenCallback = _a.childrenCallback, onChange = _a.onChange, onClick = _a.onClick, initCallback = _a.initCallback, isCategorySelected = _a.isCategorySelected, isCategoryReadOnly = _a.isCategoryReadOnly, rest = __rest(_a, ["categoryTreeCode", "init", "childrenCallback", "onChange", "onClick", "initCallback", "isCategorySelected", "isCategoryReadOnly"]);
    var _b = React.useState(), tree = _b[0], setTree = _b[1];
    var recursiveGetFirstSelectedCategoryLabel = function (category) {
        if (isCategorySelected &&
            isCategorySelected({
                id: category.id,
                code: category.code,
                label: category.label,
            }, null)) {
            return category.label;
        }
        return (category.children || []).reduce(function (previous, subCategory) { return previous || recursiveGetFirstSelectedCategoryLabel(subCategory); }, undefined);
    };
    React.useEffect(function () {
        setTree(undefined);
        init(categoryTreeCode).then(function (tree) {
            setTree(undefined);
            setTree(tree);
            if (initCallback) {
                initCallback(tree.label, recursiveGetFirstSelectedCategoryLabel(tree));
            }
        });
    }, [categoryTreeCode]);
    var recursiveCallback = function (tree, value, callback) {
        var newTree = __assign({}, tree);
        if (newTree.code === value) {
            newTree = callback(newTree);
        }
        if (newTree.children) {
            newTree.children = newTree.children.map(function (child) { return recursiveCallback(child, value, callback); });
        }
        return newTree;
    };
    var internalSetChecked = function (value, selected) {
        onChange === null || onChange === void 0 ? void 0 : onChange(value, selected);
        setTree(function (tree) { return (tree ? recursiveCallback(tree, value, function (node) { return (__assign(__assign({}, node), { selected: selected })); }) : undefined); });
    };
    var internalSetChildren = function (value, children) {
        setTree(function (tree) { return (tree ? recursiveCallback(tree, value, function (node) { return (__assign(__assign({}, node), { children: children })); }) : undefined); });
    };
    if (!tree) {
        return React.createElement(Tree, __assign({ value: "", label: "", isLoading: true }, rest));
    }
    return (React.createElement(RecursiveCategoryTree, __assign({ tree: tree, parentTree: null, childrenCallback: childrenCallback, onClick: onClick, isCategorySelected: isCategorySelected, isCategoryReadOnly: isCategoryReadOnly, internalSetChecked: internalSetChecked, internalSetChildren: internalSetChildren }, rest)));
};
export { CategoryTree };
//# sourceMappingURL=CategoryTree.js.map
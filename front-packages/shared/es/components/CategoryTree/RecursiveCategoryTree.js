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
import { Tree } from 'akeneo-design-system/lib/components/Tree/Tree';
var RecursiveCategoryTree = function (_a) {
    var _b;
    var tree = _a.tree, parentTree = _a.parentTree, childrenCallback = _a.childrenCallback, onClick = _a.onClick, isCategorySelected = _a.isCategorySelected, isCategoryReadOnly = _a.isCategoryReadOnly, internalSetChecked = _a.internalSetChecked, internalSetChildren = _a.internalSetChildren, rest = __rest(_a, ["tree", "parentTree", "childrenCallback", "onClick", "isCategorySelected", "isCategoryReadOnly", "internalSetChecked", "internalSetChildren"]);
    var _c = React.useState((_b = tree.loading) !== null && _b !== void 0 ? _b : false), loading = _c[0], setIsLoading = _c[1];
    var handleOpen = React.useCallback(function () {
        if (typeof tree.children === 'undefined') {
            setIsLoading(true);
            childrenCallback(tree.id).then(function (children) {
                setIsLoading(false);
                internalSetChildren(tree.code, children);
            });
        }
    }, [tree, internalSetChildren, childrenCallback]);
    var handleChange = function (categoryValue, checked) {
        internalSetChecked(categoryValue.code, checked);
    };
    return (React.createElement(Tree, __assign({ label: tree.label, value: {
            id: tree.id,
            code: tree.code,
            label: tree.label,
        }, isLoading: loading, selected: isCategorySelected ? isCategorySelected(tree, parentTree) : tree.selected, readOnly: isCategoryReadOnly ? isCategoryReadOnly(tree, parentTree) : tree.readOnly, selectable: tree.selectable, isLeaf: Array.isArray(tree.children) && tree.children.length === 0, onChange: handleChange, onOpen: handleOpen, onClick: onClick }, rest), tree.children &&
        tree.children.map(function (childNode) {
            return (React.createElement(RecursiveCategoryTree, { key: childNode.id, tree: childNode, parentTree: { code: tree.code, parent: parentTree }, childrenCallback: childrenCallback, onClick: onClick, isCategorySelected: isCategorySelected, isCategoryReadOnly: isCategoryReadOnly, internalSetChildren: internalSetChildren, internalSetChecked: internalSetChecked }));
        })));
};
export { RecursiveCategoryTree };
//# sourceMappingURL=RecursiveCategoryTree.js.map
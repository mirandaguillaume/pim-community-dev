"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.buildTreeNodeFromCategoryTree = exports.convertToCategoryTree = void 0;
var convertToCategoryTree = function (tree) {
    return {
        id: parseInt(tree.attr.id.substring(5)),
        code: tree.attr['data-code'],
        label: tree.data,
        isRoot: tree.state.match(/root/) !== null,
        isLeaf: tree.state.match(/leaf/) !== null,
        children: tree.children !== undefined ? tree.children.map(function (subtree) { return convertToCategoryTree(subtree); }) : [],
    };
};
exports.convertToCategoryTree = convertToCategoryTree;
var buildTreeNodeFromCategoryTree = function (categoryTree, parent) {
    if (parent === void 0) { parent = null; }
    return {
        identifier: categoryTree.id,
        label: categoryTree.label,
        childrenIds: Array.isArray(categoryTree.children) ? categoryTree.children.map(function (child) { return child.id; }) : [],
        data: categoryTree,
        parentId: parent,
        type: categoryTree.isRoot ? 'root' : categoryTree.isLeaf ? 'leaf' : 'node',
        childrenStatus: categoryTree.children && categoryTree.children.length > 0 ? 'loaded' : 'idle',
    };
};
exports.buildTreeNodeFromCategoryTree = buildTreeNodeFromCategoryTree;
//# sourceMappingURL=Category.js.map
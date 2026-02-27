"use strict";
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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.buildNodesFromCategoryTree = exports.findLoadedDescendantsIdentifiers = exports.isDescendantOf = exports.update = exports.findRoot = exports.findOneByIdentifier = exports.findByIdentifiers = void 0;
var models_1 = require("../models");
var findRoot = function (treeNodes) {
    return treeNodes.find(function (treeNode) { return treeNode.type === 'root'; });
};
exports.findRoot = findRoot;
var findByIdentifiers = function (treeNodes, identifiers) {
    var nodes = treeNodes.filter(function (treeNode) { return identifiers.includes(treeNode.identifier); });
    return identifiers
        .map(function (identifier) { return nodes.find(function (n) { return n.identifier === identifier; }); })
        .filter(function (node) { return !!node; });
};
exports.findByIdentifiers = findByIdentifiers;
var findOneByIdentifier = function (treeNodes, identifier) {
    return findByIdentifiers(treeNodes, [identifier])[0] || undefined;
};
exports.findOneByIdentifier = findOneByIdentifier;
var isDescendantOf = function (treeNodes, identifier, parentId) {
    var node = findOneByIdentifier(treeNodes, identifier);
    if (!node || node.parentId === null) {
        return false;
    }
    if (node.parentId === parentId) {
        return true;
    }
    return isDescendantOf(treeNodes, identifier, node.parentId);
};
exports.isDescendantOf = isDescendantOf;
var findLoadedDescendantsIdentifiers = function (treeNodes, parent) {
    if (parent.childrenIds.length === 0 || parent.childrenStatus !== 'loaded') {
        return [];
    }
    var descendantsIdentifiers = [];
    parent.childrenIds.forEach(function (childId) {
        descendantsIdentifiers.push(childId);
        var child = findOneByIdentifier(treeNodes, childId);
        if (child) {
            descendantsIdentifiers = __spreadArray(__spreadArray([], __read(descendantsIdentifiers), false), __read(findLoadedDescendantsIdentifiers(treeNodes, child)), false);
        }
    });
    return descendantsIdentifiers;
};
exports.findLoadedDescendantsIdentifiers = findLoadedDescendantsIdentifiers;
var update = function (treeNodes, updatedNode) {
    return __spreadArray(__spreadArray([], __read(treeNodes.filter(function (node) { return node.identifier !== updatedNode.identifier; })), false), [updatedNode], false);
};
exports.update = update;
var buildNodesFromCategoryTree = function (root) {
    var nodes = [(0, models_1.buildTreeNodeFromCategoryTree)(root)];
    if (Array.isArray(root.children)) {
        addToNodes(nodes, root.children, root.id);
    }
    return nodes;
};
exports.buildNodesFromCategoryTree = buildNodesFromCategoryTree;
var addToNodes = function (nodes, children, parentId) {
    children.forEach(function (child) {
        nodes.push((0, models_1.buildTreeNodeFromCategoryTree)(child, parentId));
        if (child.children) {
            addToNodes(nodes, child.children, child.id);
        }
    });
    return nodes;
};
//# sourceMappingURL=treeHelper.js.map
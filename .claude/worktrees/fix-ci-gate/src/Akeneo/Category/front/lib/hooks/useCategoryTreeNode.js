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
exports.useCategoryTreeNode = void 0;
var react_1 = require("react");
var components_1 = require("../components");
var models_1 = require("../models");
var helpers_1 = require("../helpers");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var infrastructure_1 = require("../infrastructure");
var useCategoryTreeNode = function (id) {
    var router = (0, shared_1.useRouter)();
    var _a = (0, react_1.useContext)(components_1.CategoryTreeContext), nodes = _a.nodes, setNodes = _a.setNodes, rest = __rest(_a, ["nodes", "setNodes"]);
    var _b = __read((0, react_1.useState)(null), 2), move = _b[0], setMove = _b[1];
    var node = (0, react_1.useMemo)(function () { return (0, helpers_1.findOneByIdentifier)(nodes, id); }, [id, nodes]);
    var children = (0, react_1.useMemo)(function () { return (!node ? [] : (0, helpers_1.findByIdentifiers)(nodes, node.childrenIds)); }, [node, nodes]);
    var _c = __read((0, akeneo_design_system_1.useBooleanState)(children.length > 0), 3), isOpen = _c[0], open = _c[1], close = _c[2];
    var url = (0, shared_1.useRoute)('pim_enrich_categorytree_children', {
        _format: 'json',
        id: id.toString(),
        select_node_id: '-1',
        with_items_count: '0',
        include_parent: '0',
        include_sub: '0',
        context: 'manage',
    });
    var _d = __read((0, shared_1.useFetch)(url), 3), childrenData = _d[0], loadChildren = _d[1], loadChildrenStatus = _d[2];
    var getCategoryPosition = function (treeNode) {
        if (!treeNode.parentId) {
            return 0;
        }
        var categoryParent = (0, helpers_1.findOneByIdentifier)(nodes, treeNode.parentId);
        return (categoryParent === null || categoryParent === void 0 ? void 0 : categoryParent.childrenIds.indexOf(treeNode.identifier)) || 0;
    };
    var moveTo = (0, react_1.useCallback)(function (movedCategoryId, target, onMove) {
        var targetParentNode = (0, helpers_1.findOneByIdentifier)(nodes, target.position === 'in' ? target.identifier : target.parentId);
        if (!targetParentNode) {
            console.error("Parent node " + target.parentId + " not found to set the move of node " + target.identifier);
            return;
        }
        setMove({
            identifier: movedCategoryId,
            target: target,
            status: target.position === 'in' && targetParentNode.type === 'node' && targetParentNode.childrenStatus === 'idle'
                ? 'pending'
                : 'ready',
            onMove: onMove,
        });
    }, [nodes]);
    var doMove = (0, react_1.useCallback)(function (move) { return __awaiter(void 0, void 0, void 0, function () {
        var identifier, target, status, movedNode, targetParentNode, originalParentNode, newNodesList, parentChildrenIds, movedIndex, updateOriginalParentChildren, persistSuccess;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    identifier = move.identifier, target = move.target, status = move.status;
                    if (status !== 'ready') {
                        return [2];
                    }
                    if (identifier === target.identifier) {
                        return [2];
                    }
                    movedNode = (0, helpers_1.findOneByIdentifier)(nodes, identifier);
                    if (!movedNode) {
                        console.error("Failed to move node " + identifier + " : Node not found");
                        return [2];
                    }
                    targetParentNode = (0, helpers_1.findOneByIdentifier)(nodes, target.position === 'in' ? target.identifier : target.parentId);
                    if (!targetParentNode) {
                        console.error("Failed to move node " + identifier + " : Target parent node " + target.parentId + " not found");
                        return [2];
                    }
                    if (!movedNode.parentId) {
                        console.error("Failed to move node " + identifier + " : Can not move root node");
                        return [2];
                    }
                    originalParentNode = (0, helpers_1.findOneByIdentifier)(nodes, movedNode.parentId);
                    if (!originalParentNode) {
                        console.error("Failed to move node " + identifier + " : Moved parent Node " + movedNode.parentId + " not found");
                        return [2];
                    }
                    newNodesList = nodes;
                    parentChildrenIds = targetParentNode.childrenIds.filter(function (id) { return id !== movedNode.identifier; });
                    movedIndex = parentChildrenIds.findIndex(function (id) { return id === target.identifier; });
                    parentChildrenIds.splice(target.position === 'in' ? 0 : target.position === 'after' ? movedIndex + 1 : movedIndex, 0, movedNode.identifier);
                    newNodesList = (0, helpers_1.update)(newNodesList, __assign(__assign({}, targetParentNode), { childrenIds: parentChildrenIds, type: targetParentNode.type === 'leaf' ? 'node' : targetParentNode.type, childrenStatus: 'loaded' }));
                    newNodesList = (0, helpers_1.update)(newNodesList, __assign(__assign({}, movedNode), { parentId: targetParentNode.identifier }));
                    if (originalParentNode.identifier !== targetParentNode.identifier) {
                        updateOriginalParentChildren = originalParentNode.childrenIds.filter(function (id) { return id !== movedNode.identifier; });
                        newNodesList = (0, helpers_1.update)(newNodesList, __assign(__assign({}, originalParentNode), { childrenIds: updateOriginalParentChildren, type: originalParentNode.type !== 'root' ? (updateOriginalParentChildren.length > 0 ? 'node' : 'leaf') : 'root' }));
                    }
                    setNodes(newNodesList);
                    return [4, (0, infrastructure_1.moveCategory)(router, {
                            identifier: identifier,
                            parentId: target.position === 'in' ? target.identifier : target.parentId,
                            previousCategoryId: determineAfterWhichCategoryIdentifierToMove(target, targetParentNode.childrenIds),
                        })];
                case 1:
                    persistSuccess = _a.sent();
                    if (!persistSuccess) {
                        console.error("Failed to persist node " + identifier);
                    }
                    return [2];
            }
        });
    }); }, [nodes]);
    var determineAfterWhichCategoryIdentifierToMove = function (target, childrenIds) {
        if (target.position === 'after') {
            return target.identifier;
        }
        if (target.position === 'before') {
            var targetIndex = childrenIds.indexOf(target.identifier) - 1;
            if (targetIndex >= 0 && childrenIds[targetIndex]) {
                return childrenIds[targetIndex];
            }
        }
        return null;
    };
    var onDeleteCategory = function () {
        if (!node || !node.parentId) {
            return;
        }
        var nodesToRemove = __spreadArray([node.identifier], __read((0, helpers_1.findLoadedDescendantsIdentifiers)(nodes, node)), false);
        var updatedNodes = nodes.filter(function (treeNode) { return !nodesToRemove.includes(treeNode.identifier); });
        var parentNode = (0, helpers_1.findOneByIdentifier)(nodes, node.parentId);
        if (!parentNode) {
            setNodes(updatedNodes);
            return;
        }
        var updatedParentChildren = parentNode.childrenIds.filter(function (childId) { return childId !== node.identifier; });
        var updatedParent = __assign(__assign({}, parentNode), { childrenIds: updatedParentChildren, type: parentNode.type !== 'root' ? (updatedParentChildren.length > 0 ? 'node' : 'leaf') : 'root' });
        setNodes((0, helpers_1.update)(updatedNodes, updatedParent));
    };
    var onCreateCategory = (0, react_1.useCallback)(function () {
        if (node) {
            setNodes(function (nodes) {
                var updatedNodes = (0, helpers_1.update)(nodes, __assign(__assign({}, node), { childrenStatus: 'to-reload' }));
                return __spreadArray([], __read(updatedNodes), false);
            });
            open();
        }
    }, [node]);
    (0, react_1.useEffect)(function () {
        if (!node || !Array.isArray(childrenData)) {
            return;
        }
        var newChildren = childrenData.map(function (child) {
            return (0, models_1.buildTreeNodeFromCategoryTree)((0, models_1.convertToCategoryTree)(child), node.identifier);
        });
        setNodes(function (nodes) {
            var updatedNodes = (0, helpers_1.update)(nodes, __assign(__assign({}, node), { childrenIds: newChildren.map(function (child) { return child.identifier; }), childrenStatus: 'loaded', type: node.type !== 'root' ? (newChildren.length > 0 ? 'node' : 'leaf') : 'root' }));
            return (0, akeneo_design_system_1.arrayUnique)(__spreadArray(__spreadArray([], __read(updatedNodes), false), __read(newChildren), false), function (node, currentNode) { return node.identifier === currentNode.identifier; });
        });
    }, [childrenData]);
    (0, react_1.useEffect)(function () {
        if ((node === null || node === void 0 ? void 0 : node.childrenStatus) === 'to-reload') {
            loadChildren();
        }
    }, [node === null || node === void 0 ? void 0 : node.childrenStatus]);
    (0, react_1.useEffect)(function () {
        if (move === null) {
            return;
        }
        if (move.status === 'pending') {
            var loadChildrenBeforeMove = function () { return __awaiter(void 0, void 0, void 0, function () {
                return __generator(this, function (_a) {
                    switch (_a.label) {
                        case 0: return [4, loadChildren()];
                        case 1:
                            _a.sent();
                            setMove(__assign(__assign({}, move), { status: 'ready' }));
                            return [2];
                    }
                });
            }); };
            loadChildrenBeforeMove();
            return;
        }
        if (move.status === 'ready') {
            doMove(move);
            move.onMove();
            setMove(null);
            return;
        }
    }, [move]);
    (0, react_1.useEffect)(function () {
        if (!node) {
            return;
        }
        if (loadChildrenStatus === 'error') {
            var newNodesList = (0, helpers_1.update)(nodes, __assign(__assign({}, node), { childrenStatus: 'idle' }));
            setNodes(newNodesList);
        }
        if (loadChildrenStatus === 'fetching') {
            var newNodesList = (0, helpers_1.update)(nodes, __assign(__assign({}, node), { childrenStatus: 'loading' }));
            setNodes(newNodesList);
        }
    }, [loadChildrenStatus]);
    return __assign({ node: node, children: children, loadChildren: loadChildren, moveTo: moveTo, getCategoryPosition: getCategoryPosition, onDeleteCategory: onDeleteCategory, onCreateCategory: onCreateCategory, isOpen: isOpen, open: open, close: close }, rest);
};
exports.useCategoryTreeNode = useCategoryTreeNode;
//# sourceMappingURL=useCategoryTreeNode.js.map
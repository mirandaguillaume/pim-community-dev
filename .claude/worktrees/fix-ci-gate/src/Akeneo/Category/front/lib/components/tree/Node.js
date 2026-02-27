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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Node = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var base_1 = require("./base");
var hooks_1 = require("../../hooks");
var Node = function (_a) {
    var id = _a.id, label = _a.label, code = _a.code, followCategory = _a.followCategory, addCategory = _a.addCategory, deleteCategory = _a.deleteCategory, _b = _a.orderable, orderable = _b === void 0 ? false : _b, _c = _a.index, index = _c === void 0 ? 0 : _c;
    var _d = (0, hooks_1.useCategoryTreeNode)(id), node = _d.node, children = _d.children, loadChildren = _d.loadChildren, moveTo = _d.moveTo, onDeleteCategory = _d.onDeleteCategory, onCreateCategory = _d.onCreateCategory, isOpen = _d.isOpen, open = _d.open, close = _d.close;
    var _e = (0, hooks_1.useDragTreeNode)(node, index), isDragged = _e.isDragged, isDraggable = _e.isDraggable, dragProps = __rest(_e, ["isDragged", "isDraggable"]);
    var _f = (0, hooks_1.useDropTreeNode)(node, moveTo), placeholderPosition = _f.placeholderPosition, dropProps = __rest(_f, ["placeholderPosition"]);
    var translate = (0, shared_1.useTranslate)();
    var countProductsBeforeDeleteCategory = (0, hooks_1.useCountProductsBeforeDeleteCategory)(id);
    if (node === undefined) {
        return null;
    }
    return ((0, jsx_runtime_1.jsxs)(base_1.Tree, __assign({ value: node, label: label, _isRoot: node.type === 'root', isLeaf: node.type === 'leaf', isLoading: node.childrenStatus === 'loading', onClick: !followCategory ? undefined : function (_a) {
            var data = _a.data;
            return followCategory(data);
        }, disabled: isDragged(), selected: placeholderPosition === 'middle', placeholderPosition: placeholderPosition, isOpen: node.type === 'root' ? true : isOpen, open: function () {
            open();
            if (node.childrenStatus === 'idle') {
                loadChildren();
            }
        }, close: close, draggable: isDraggable }, dragProps, dropProps, { children: [(addCategory || deleteCategory) && ((0, jsx_runtime_1.jsxs)(base_1.Tree.Actions, { children: [addCategory && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ ghost: true, level: 'primary', size: "small", onClick: function (event) {
                            event.stopPropagation();
                            addCategory(node.data.code, onCreateCategory);
                        } }, { children: translate('pim_enrich.entity.category.new_category') }), void 0)), deleteCategory && node.type !== 'root' && ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Button, __assign({ ghost: true, level: 'danger', size: "small", onClick: function (event) {
                            event.stopPropagation();
                            countProductsBeforeDeleteCategory(function (nbProducts) {
                                return deleteCategory(id, label, code, nbProducts, onDeleteCategory);
                            });
                        } }, { children: translate('pim_common.delete') }), void 0))] }, "category-actions-" + id)), children.map(function (child, index) { return ((0, jsx_runtime_1.jsx)(react_1.default.Fragment, { children: (0, jsx_runtime_1.jsx)(Node, { id: child.identifier, label: child.label, code: child.code, followCategory: followCategory, addCategory: addCategory, deleteCategory: deleteCategory, orderable: orderable, index: index }, void 0) }, "category-node-" + id + "-" + child.identifier)); })] }), void 0));
};
exports.Node = Node;
//# sourceMappingURL=Node.js.map
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
Object.defineProperty(exports, "__esModule", { value: true });
exports.CategoryTree = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var Node_1 = require("./Node");
var providers_1 = require("../providers");
var providers_2 = require("../providers");
var base_1 = require("./base");
var CategoryTree = function (_a) {
    var root = _a.root, _b = _a.orderable, orderable = _b === void 0 ? false : _b, rest = __rest(_a, ["root", "orderable"]);
    if (root === null) {
        return (0, jsx_runtime_1.jsx)(base_1.Tree.Skeleton, {}, void 0);
    }
    return ((0, jsx_runtime_1.jsx)(providers_1.CategoryTreeProvider, __assign({ root: root }, { children: (0, jsx_runtime_1.jsx)(providers_2.OrderableTreeProvider, __assign({ isActive: orderable }, { children: (0, jsx_runtime_1.jsx)(Node_1.Node, __assign({ id: root.id, label: root.label, code: root.code, orderable: orderable }, rest), void 0) }), void 0) }), void 0));
};
exports.CategoryTree = CategoryTree;
//# sourceMappingURL=CategoryTree.js.map
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
exports.CategoryTreeContext = exports.CategoryTreeProvider = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var helpers_1 = require("../../helpers");
var CategoryTreeContext = (0, react_1.createContext)({
    nodes: [],
    setNodes: function () { },
});
exports.CategoryTreeContext = CategoryTreeContext;
var CategoryTreeProvider = function (_a) {
    var children = _a.children, root = _a.root;
    var _b = __read((0, react_1.useState)([]), 2), nodes = _b[0], setNodes = _b[1];
    (0, react_1.useEffect)(function () {
        var nodes = (0, helpers_1.buildNodesFromCategoryTree)(root);
        setNodes(nodes);
    }, [root]);
    var state = {
        nodes: nodes,
        setNodes: setNodes,
    };
    return (0, jsx_runtime_1.jsx)(CategoryTreeContext.Provider, __assign({ value: state }, { children: children }), void 0);
};
exports.CategoryTreeProvider = CategoryTreeProvider;
//# sourceMappingURL=CategoryTreeProvider.js.map
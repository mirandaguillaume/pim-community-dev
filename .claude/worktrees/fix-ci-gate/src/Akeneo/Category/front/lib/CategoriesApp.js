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
Object.defineProperty(exports, "__esModule", { value: true });
exports.CategoriesApp = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var react_query_1 = require("react-query");
var react_router_dom_1 = require("react-router-dom");
var ErrorBoundary_1 = require("./ErrorBoundary");
var components_1 = require("./components");
var SaveStatusProvider_1 = require("./components/providers/SaveStatusProvider");
var TemplateFormProvider_1 = require("./components/providers/TemplateFormProvider");
var UnsavedChangeGuard_1 = require("./components/templates/UnsavedChangeGuard");
var pages_1 = require("./pages");
var apiFetch_1 = require("./tools/apiFetch");
var useErrorBoundary = function (error) { return false === error instanceof apiFetch_1.BadRequestError; };
var CategoriesApp = function (_a) {
    var setCanLeavePage = _a.setCanLeavePage, setLeavePageMessage = _a.setLeavePageMessage;
    var queryClient = new react_query_1.QueryClient({
        defaultOptions: {
            queries: { useErrorBoundary: useErrorBoundary },
            mutations: { useErrorBoundary: useErrorBoundary },
        },
    });
    return ((0, jsx_runtime_1.jsx)(react_1.StrictMode, { children: (0, jsx_runtime_1.jsx)(ErrorBoundary_1.ErrorBoundary, { children: (0, jsx_runtime_1.jsx)(react_query_1.QueryClientProvider, __assign({ client: queryClient }, { children: (0, jsx_runtime_1.jsx)(components_1.CanLeavePageProvider, __assign({ setCanLeavePage: setCanLeavePage, setLeavePageMessage: setLeavePageMessage }, { children: (0, jsx_runtime_1.jsx)(react_router_dom_1.HashRouter, __assign({ basename: "/enrich/product-category-tree" }, { children: (0, jsx_runtime_1.jsxs)(react_router_dom_1.Switch, { children: [(0, jsx_runtime_1.jsx)(react_router_dom_1.Route, __assign({ path: "/:treeId/tree" }, { children: (0, jsx_runtime_1.jsx)(pages_1.CategoriesTreePage, {}, void 0) }), void 0), (0, jsx_runtime_1.jsx)(react_router_dom_1.Route, __assign({ path: "/:categoryId/edit" }, { children: (0, jsx_runtime_1.jsx)(components_1.EditCategoryProvider, { children: (0, jsx_runtime_1.jsx)(pages_1.CategoryEditPage, {}, void 0) }, void 0) }), void 0), (0, jsx_runtime_1.jsx)(react_router_dom_1.Route, __assign({ path: "/:treeId/template/:templateId" }, { children: (0, jsx_runtime_1.jsxs)(SaveStatusProvider_1.SaveStatusProvider, { children: [(0, jsx_runtime_1.jsx)(UnsavedChangeGuard_1.UnsavedChangesGuard, {}, void 0), (0, jsx_runtime_1.jsx)(TemplateFormProvider_1.TemplateFormProvider, { children: (0, jsx_runtime_1.jsx)(pages_1.TemplatePage, {}, void 0) }, void 0)] }, void 0) }), void 0), (0, jsx_runtime_1.jsx)(react_router_dom_1.Route, __assign({ path: "/" }, { children: (0, jsx_runtime_1.jsx)(pages_1.CategoriesIndex, {}, void 0) }), void 0)] }, void 0) }), void 0) }), void 0) }), void 0) }, void 0) }, void 0));
};
exports.CategoriesApp = CategoriesApp;
//# sourceMappingURL=CategoriesApp.js.map
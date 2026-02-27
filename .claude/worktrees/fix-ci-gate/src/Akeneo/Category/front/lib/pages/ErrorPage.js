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
exports.ErrorPage = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var react_1 = require("react");
var apiFetch_1 = require("../tools/apiFetch");
var ErrorPage = function (_a) {
    var error = _a.error;
    var translate = (0, shared_1.useTranslate)();
    (0, react_1.useEffect)(function () {
        if (error instanceof apiFetch_1.UnauthorizedError) {
            globalThis.location.reload();
            return;
        }
        if (error instanceof apiFetch_1.ForbiddenError) {
            globalThis.location.reload();
            return;
        }
    }, [error]);
    return ((0, jsx_runtime_1.jsxs)(jsx_runtime_1.Fragment, { children: [(0, jsx_runtime_1.jsx)(shared_1.PageHeader, {}, void 0), (0, jsx_runtime_1.jsx)(shared_1.PageContent, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Placeholder, __assign({ illustration: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.ClientErrorIllustration, {}, void 0), size: "large", title: translate('akeneo.category.unknown_error.title') }, { children: translate('akeneo.category.unknown_error.message') }), void 0) }, void 0)] }, void 0));
};
exports.ErrorPage = ErrorPage;
//# sourceMappingURL=ErrorPage.js.map
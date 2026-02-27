"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TreeSkeleton = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: grid;\n  grid-row-gap: 10px;\n\n  > div {\n    height: 54px;\n  }\n"], ["\n  display: grid;\n  grid-row-gap: 10px;\n\n  > div {\n    height: 54px;\n  }\n"])));
var TreeSkeleton = function () {
    return ((0, jsx_runtime_1.jsx)(Container, { children: __spreadArray([], __read(Array(5)), false).map(function (_e, i) { return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.SkeletonPlaceholder, {}, i)); }) }, void 0));
};
exports.TreeSkeleton = TreeSkeleton;
var templateObject_1;
//# sourceMappingURL=TreeSkeleton.js.map